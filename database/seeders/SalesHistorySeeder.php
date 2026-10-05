<?php

namespace Database\Seeders;

use App\Models\Closing;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesHistorySeeder extends Seeder
{
    /**
     * Number of days of history to generate (defaults to roughly two months).
     */
    public int $days = 60;

    protected static bool $running = false;

    protected float $baseRate = 40.0;

    protected float $commissionRate = 40.0;

    protected int $adminId = 0;

    /** @var array<int, int> */
    protected array $stock = [];

    /** @var array<int, string> */
    protected array $paymentMethods = [];

    /** @var array<int, string> */
    protected array $clientNotes = [
        'Cliente frecuente',
        'Pasó sin cita',
        'Cliente nuevo',
        'Referido por un amigo',
        'Pidió degradado alto',
        'Corte + barba',
        'Cliente del barrio',
    ];

    public function days(int $days): static
    {
        $this->days = max(1, $days);

        return $this;
    }

    public function run(): void
    {
        if (static::$running) {
            return;
        }

        static::$running = true;

        if (app()->environment('production')) {
            $this->command?->warn('SalesHistorySeeder no se ejecuta en el entorno de producción.');

            return;
        }

        if (! $this->baseDataExists()) {
            $this->call(DatabaseSeeder::class);
        }

        $admins = User::query()->where('role', User::ROLE_ADMIN)->get();
        $barbers = User::query()->where('role', User::ROLE_BARBER)->where('active', true)->get();
        $services = Service::query()->where('active', true)->get();
        $products = Product::query()->where('active', true)->get();

        if ($admins->isEmpty() || $barbers->isEmpty() || $services->isEmpty() || $products->isEmpty()) {
            $this->command?->warn('Faltan datos base (administrador, barberos, servicios o productos).');

            return;
        }

        $this->adminId = (int) $admins->first()->id;
        $this->commissionRate = (float) setting('commission_rate', 40);
        $this->baseRate = $this->resolveBaseRate();
        $this->paymentMethods = payment_methods();

        $this->resetHistory();

        foreach ($products as $product) {
            $this->stock[$product->id] = (int) $product->stock;
            $this->recordMovement($product, StockMovement::TYPE_IN, (int) $product->stock, 'Inventario inicial', now());
        }

        $start = now()->subDays($this->days - 1)->startOfDay();
        $end = now()->startOfDay();

        DB::transaction(function () use ($start, $end, $admins, $barbers, $services, $products) {
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $this->seedDay($date, $admins, $barbers, $services, $products);
            }
        });

        foreach ($products as $product) {
            $product->update(['stock' => $this->stock[$product->id] ?? 0]);
        }

        $this->command?->info(sprintf(
            'Historial generado: %d ventas en los últimos %d días (%d cortes, %d ventas de productos).',
            Sale::query()->count(),
            $this->days,
            Sale::query()->where('type', Sale::TYPE_SERVICE)->count(),
            Sale::query()->where('type', Sale::TYPE_PRODUCT)->count(),
        ));
    }

    protected function seedDay(Carbon $date, Collection $admins, Collection $barbers, Collection $services, Collection $products): void
    {
        $isSunday = $date->isSunday();
        $isWeekend = $date->isFriday() || $date->isSaturday();

        $serviceCount = $isSunday ? random_int(0, 4) : ($isWeekend ? random_int(10, 20) : random_int(6, 14));
        $productCount = $isSunday ? random_int(0, 2) : ($isWeekend ? random_int(2, 6) : random_int(1, 4));

        if ($date->isToday() && now()->hour < 9) {
            $serviceCount = 0;
            $productCount = 0;
        }

        $dayRate = $this->baseRate * (1 + random_int(-15, 15) / 1000);

        for ($i = 0; $i < $serviceCount; $i++) {
            $service = $services->random();
            $barber = $barbers->random();
            $registeredBy = random_int(1, 100) <= 50 ? $admins->random() : $barber;

            $this->createServiceSale($this->randomTime($date), $registeredBy, $barber, $service, $dayRate);
        }

        for ($i = 0; $i < $productCount; $i++) {
            $registeredBy = random_int(1, 100) <= 60 ? $admins->random() : $barbers->random();

            $this->createProductSale($this->randomTime($date), $registeredBy, $products, $dayRate);
        }
    }

    protected function createServiceSale(Carbon $at, User $registeredBy, User $barber, Service $service, float $rate): void
    {
        $total = (float) $service->price;
        $commission = round($total * $this->commissionRate / 100, 2);

        $sale = new Sale([
            'user_id' => $registeredBy->id,
            'barber_id' => $barber->id,
            'type' => Sale::TYPE_SERVICE,
            'exchange_rate' => round($rate, 4),
            'commission_rate' => $this->commissionRate,
            'total_usd' => $total,
            'total_ves' => round($total * $rate, 2),
            'barber_commission_usd' => $commission,
            'shop_amount_usd' => round($total - $commission, 2),
            'payment_method' => $this->randomPaymentMethod(),
            'payment_currency' => $this->randomCurrency(),
            'status' => Sale::STATUS_COMPLETED,
            'notes' => $this->randomNote(),
            'sold_at' => $at,
        ]);
        $this->stampAndSave($sale, $at);

        $sale->items()->create([
            'item_type' => SaleItem::TYPE_SERVICE,
            'service_id' => $service->id,
            'name' => $service->name,
            'quantity' => 1,
            'unit_price_usd' => $total,
            'line_total_usd' => $total,
        ]);
    }

    protected function createProductSale(Carbon $at, User $registeredBy, Collection $products, float $rate): void
    {
        $available = $products->filter(fn (Product $product) => ($this->stock[$product->id] ?? 0) > 0);

        if ($available->isEmpty()) {
            foreach ($products as $product) {
                if (($this->stock[$product->id] ?? 0) < 5) {
                    $this->restock($product, $at);
                }
            }

            $available = $products->filter(fn (Product $product) => ($this->stock[$product->id] ?? 0) > 0);
        }

        if ($available->isEmpty()) {
            return;
        }

        $lines = [];

        foreach ($available->shuffle()->take(random_int(1, 3)) as $product) {
            $quantity = min($this->stock[$product->id], random_int(1, 2));

            if ($quantity < 1) {
                continue;
            }

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => round((float) $product->price * $quantity, 2),
            ];
        }

        if (empty($lines)) {
            return;
        }

        $total = array_sum(array_column($lines, 'line_total'));

        $sale = new Sale([
            'user_id' => $registeredBy->id,
            'barber_id' => null,
            'type' => Sale::TYPE_PRODUCT,
            'exchange_rate' => round($rate, 4),
            'commission_rate' => $this->commissionRate,
            'total_usd' => $total,
            'total_ves' => round($total * $rate, 2),
            'barber_commission_usd' => 0,
            'shop_amount_usd' => $total,
            'payment_method' => $this->randomPaymentMethod(),
            'payment_currency' => $this->randomCurrency(),
            'status' => Sale::STATUS_COMPLETED,
            'notes' => $this->randomNote(),
            'sold_at' => $at,
        ]);
        $this->stampAndSave($sale, $at);

        foreach ($lines as $line) {
            /** @var Product $product */
            $product = $line['product'];

            $sale->items()->create([
                'item_type' => SaleItem::TYPE_PRODUCT,
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $line['quantity'],
                'unit_price_usd' => $product->price,
                'line_total_usd' => $line['line_total'],
            ]);

            $this->stock[$product->id] -= $line['quantity'];
            $this->recordMovement($product, StockMovement::TYPE_OUT, -$line['quantity'], 'Venta '.$sale->code, $at, $registeredBy->id);

            if ($this->stock[$product->id] <= (int) $product->min_stock) {
                $this->restock($product, $at);
            }
        }
    }

    protected function restock(Product $product, Carbon $at): void
    {
        $target = max((int) $product->min_stock * 6, 20) + random_int(0, 10);
        $current = $this->stock[$product->id] ?? 0;
        $quantity = max(0, $target - $current);

        if ($quantity <= 0) {
            return;
        }

        $this->stock[$product->id] = $current + $quantity;
        $this->recordMovement($product, StockMovement::TYPE_IN, $quantity, 'Reposición de inventario', $at);
    }

    protected function recordMovement(Product $product, string $type, int $quantity, string $note, Carbon $at, ?int $userId = null): void
    {
        $movement = new StockMovement([
            'product_id' => $product->id,
            'user_id' => $userId ?? $this->adminId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_after' => $this->stock[$product->id] ?? 0,
            'note' => $note,
        ]);
        $movement->created_at = $at;
        $movement->updated_at = $at;
        $movement->save();
    }

    protected function stampAndSave(Sale $sale, Carbon $at): void
    {
        $sale->code = 'TMP-'.Str::uuid();
        $sale->created_at = $at;
        $sale->updated_at = $at;
        $sale->save();

        $sale->code = 'V-'.str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT);
        $sale->save();
    }

    protected function randomTime(Carbon $date): Carbon
    {
        $maxHour = $date->isToday() ? min(19, max(9, now()->hour)) : 19;

        return $date->copy()->setTime(random_int(9, $maxHour), random_int(0, 59), random_int(0, 59));
    }

    protected function randomPaymentMethod(): ?string
    {
        if (empty($this->paymentMethods)) {
            return null;
        }

        return $this->paymentMethods[array_rand($this->paymentMethods)];
    }

    protected function randomCurrency(): string
    {
        return random_int(1, 100) <= 70 ? 'USD' : 'VES';
    }

    protected function randomNote(): ?string
    {
        return random_int(1, 100) <= 35 ? $this->clientNotes[array_rand($this->clientNotes)] : null;
    }

    protected function resolveBaseRate(): float
    {
        $rate = (float) (setting('exchange_rate_manual') ?: setting('exchange_rate_fallback') ?: 0);

        if ($rate <= 0) {
            $rate = (float) DB::table('exchange_rates')->latest('fetched_at')->value('rate');
        }

        return $rate > 0 ? $rate : 40.0;
    }

    protected function resetHistory(): void
    {
        DB::transaction(function () {
            StockMovement::query()->delete();
            SaleItem::query()->delete();
            Sale::query()->delete();
            Closing::query()->delete();
        });
    }

    protected function baseDataExists(): bool
    {
        return User::query()->where('role', User::ROLE_ADMIN)->exists()
            && User::query()->where('role', User::ROLE_BARBER)->exists()
            && Service::query()->where('active', true)->exists()
            && Product::query()->where('active', true)->exists();
    }
}
