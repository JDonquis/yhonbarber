<?php

namespace App\Services;

use App\Models\Closing;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SaleService
{
    public function __construct(
        protected ExchangeRateService $rates,
        protected SettingService $settings,
    ) {}

    /**
     * Register the sale of a single haircut/service.
     */
    public function registerServiceSale(array $data, User $registeredBy): Sale
    {
        $service = Service::query()->findOrFail($data['service_id']);
        $barberId = $data['barber_id'] ?? null;
        $total = (float) $service->price;
        $commissionRate = (float) $this->settings->get('commission_rate', 0);
        $commission = round($total * $commissionRate / 100, 2);

        return DB::transaction(function () use ($service, $total, $commission, $commissionRate, $data, $registeredBy, $barberId) {
            $sale = $this->createSale([
                'user_id' => $registeredBy->id,
                'barber_id' => $barberId,
                'type' => Sale::TYPE_SERVICE,
                'commission_rate' => $commissionRate,
                'total_usd' => $total,
                'barber_commission_usd' => $commission,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_currency' => $data['payment_currency'] ?? 'USD',
                'notes' => $data['notes'] ?? null,
            ]);

            $sale->items()->create([
                'item_type' => SaleItem::TYPE_SERVICE,
                'service_id' => $service->id,
                'name' => $service->name,
                'quantity' => 1,
                'unit_price_usd' => $service->price,
                'line_total_usd' => $total,
            ]);

            return $sale;
        });
    }

    /**
     * Register the sale of one or more products, decreasing stock.
     *
     * @param  array<int, array{product_id:int, quantity:int}>  $items
     */
    public function registerProductSale(array $items, array $data, User $registeredBy): Sale
    {
        $commissionRate = (float) $this->settings->get('commission_rate', 0);
        $barberId = $data['barber_id'] ?? null;

        return DB::transaction(function () use ($items, $data, $registeredBy, $barberId, $commissionRate) {
            $total = 0;
            $lineItems = [];

            foreach ($items as $row) {
                $product = Product::query()->lockForUpdate()->findOrFail($row['product_id']);
                $quantity = (int) $row['quantity'];

                if ($quantity < 1) {
                    continue;
                }

                if ($product->stock < $quantity) {
                    throw new RuntimeException("Stock insuficiente para {$product->name}. Disponible: {$product->stock}.");
                }

                $lineTotal = round((float) $product->price * $quantity, 2);
                $total += $lineTotal;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ];
            }

            if (empty($lineItems)) {
                throw new RuntimeException('Debes agregar al menos un producto a la venta.');
            }

            $sale = $this->createSale([
                'user_id' => $registeredBy->id,
                'barber_id' => $barberId,
                'type' => Sale::TYPE_PRODUCT,
                'commission_rate' => $commissionRate,
                'total_usd' => $total,
                'barber_commission_usd' => 0,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_currency' => $data['payment_currency'] ?? 'USD',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lineItems as $line) {
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

                $newStock = $product->stock - $line['quantity'];
                $product->update(['stock' => $newStock]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $registeredBy->id,
                    'type' => StockMovement::TYPE_OUT,
                    'quantity' => -$line['quantity'],
                    'stock_after' => $newStock,
                    'note' => 'Venta '.$sale->code,
                ]);
            }

            return $sale;
        });
    }

    /**
     * Cancel a sale and restore product stock when needed.
     */
    public function cancel(Sale $sale, User $user): void
    {
        if ($sale->isCancelled()) {
            return;
        }

        DB::transaction(function () use ($sale, $user) {
            foreach ($sale->items as $item) {
                if ($item->item_type !== SaleItem::TYPE_PRODUCT || ! $item->product) {
                    continue;
                }

                $product = Product::query()->lockForUpdate()->find($item->product_id);
                $newStock = $product->stock + $item->quantity;
                $product->update(['stock' => $newStock]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'type' => StockMovement::TYPE_IN,
                    'quantity' => $item->quantity,
                    'stock_after' => $newStock,
                    'note' => 'Anulación venta '.$sale->code,
                ]);
            }

            $sale->update(['status' => Sale::STATUS_CANCELLED]);
        });
    }

    protected function createSale(array $attributes): Sale
    {
        $rate = $this->rates->current();
        $total = (float) $attributes['total_usd'];
        $commission = (float) ($attributes['barber_commission_usd'] ?? 0);

        $this->assertPeriodOpen(now());

        $sale = Sale::create($attributes + [
            'code' => 'TMP-'.Str::uuid(),
            'exchange_rate' => $rate,
            'total_ves' => round($total * $rate, 2),
            'shop_amount_usd' => round($total - $commission, 2),
            'status' => Sale::STATUS_COMPLETED,
            'sold_at' => now(),
        ]);

        $sale->update(['code' => 'V-'.str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT)]);

        return $sale;
    }

    protected function assertPeriodOpen(\DateTimeInterface $moment): void
    {
        $closed = Closing::query()
            ->where('status', Closing::STATUS_CLOSED)
            ->whereDate('period_start', '<=', $moment)
            ->whereDate('period_end', '>=', $moment)
            ->exists();

        if ($closed) {
            throw new RuntimeException('El período de esta venta ya fue cerrado. No se pueden registrar más movimientos.');
        }
    }
}
