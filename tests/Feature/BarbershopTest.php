<?php

namespace Tests\Feature;

use App\Models\Closing;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarbershopTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function barber(): User
    {
        return User::factory()->create(['role' => User::ROLE_BARBER]);
    }

    private function setting(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    private function service(float $price = 10): Service
    {
        return Service::create(['name' => 'Corte clásico', 'price' => $price, 'active' => true]);
    }

    public function test_admin_pages_render(): void
    {
        $admin = $this->admin();

        $urls = [
            route('dashboard'),
            route('services.index'),
            route('services.create'),
            route('products.index'),
            route('products.create'),
            route('barbers.index'),
            route('barbers.create'),
            route('sales.index'),
            route('sales.create-service'),
            route('sales.create-product'),
            route('closings.index'),
            route('settings.edit'),
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_barber_cannot_access_admin_pages(): void
    {
        $barber = $this->barber();

        $this->actingAs($barber)->get(route('services.index'))->assertForbidden();
        $this->actingAs($barber)->get(route('products.index'))->assertForbidden();
        $this->actingAs($barber)->get(route('settings.edit'))->assertForbidden();
    }

    public function test_service_sale_applies_global_commission(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_method' => 'Efectivo USD',
            'payment_currency' => 'USD',
        ])->assertRedirect();

        $sale = Sale::firstOrFail();

        $this->assertSame('10.00', $sale->total_usd);
        $this->assertSame('4.00', $sale->barber_commission_usd);
        $this->assertSame('6.00', $sale->shop_amount_usd);
        $this->assertSame('300.00', $sale->total_ves);
        $this->assertSame($barber->id, $sale->barber_id);
        $this->assertSame('V-000001', $sale->code);
    }

    public function test_barber_sale_forces_own_barber_id(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $barber = $this->barber();
        $other = $this->barber();

        $this->actingAs($barber)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $other->id,
            'payment_currency' => 'USD',
        ])->assertRedirect();

        $this->assertSame($barber->id, Sale::firstOrFail()->barber_id);
    }

    public function test_product_sale_decrements_stock(): void
    {
        $product = Product::create(['name' => 'Cera', 'price' => 5, 'stock' => 10, 'active' => true]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('sales.store-product'), [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            'payment_currency' => 'USD',
        ])->assertRedirect();

        $this->assertEquals(7, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'salida',
            'quantity' => -3,
            'stock_after' => 7,
        ]);
    }

    public function test_product_sale_fails_without_enough_stock(): void
    {
        $product = Product::create(['name' => 'Cera', 'price' => 5, 'stock' => 1, 'active' => true]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('sales.store-product'), [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            'payment_currency' => 'USD',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertEquals(1, $product->fresh()->stock);
        $this->assertSame(0, Sale::count());
    }

    public function test_cancelling_product_sale_restores_stock(): void
    {
        $product = Product::create(['name' => 'Cera', 'price' => 5, 'stock' => 10, 'active' => true]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('sales.store-product'), [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
            'payment_currency' => 'USD',
        ]);

        $sale = Sale::firstOrFail();

        $this->actingAs($admin)->post(route('sales.cancel', $sale))->assertRedirect();

        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertSame(Sale::STATUS_CANCELLED, $sale->fresh()->status);
    }

    public function test_closing_locks_period_and_blocks_new_sales(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ]);

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'diario',
            'date' => now()->toDateString(),
        ])->assertRedirect();

        $closing = Closing::firstOrFail();
        $this->assertSame('10.00', $closing->total_usd);
        $this->assertSame('4.00', $closing->barber_commission_usd);

        $this->actingAs($admin)->post(route('closings.close', $closing), [])->assertRedirect();
        $this->assertTrue($closing->fresh()->isClosed());

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ])->assertSessionHas('error');

        $this->assertSame(1, Sale::count());
    }

    public function test_admin_can_reopen_a_closed_period(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ]);

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'diario',
            'date' => now()->toDateString(),
        ]);

        $closing = Closing::firstOrFail();

        $this->actingAs($admin)->post(route('closings.close', $closing), [])->assertRedirect();
        $this->assertTrue($closing->fresh()->isClosed());

        $this->actingAs($admin)->post(route('closings.reopen', $closing))->assertRedirect();

        $reopened = $closing->fresh();
        $this->assertFalse($reopened->isClosed());
        $this->assertSame($admin->id, $reopened->reopened_by);
        $this->assertNotNull($reopened->reopened_at);

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ])->assertSessionMissing('error');

        $this->assertSame(2, Sale::count());
    }

    public function test_admin_can_register_barber(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('barbers.store'), [
            'name' => 'Nuevo Barbero',
            'email' => 'nuevo@yhonbarber.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'active' => '1',
        ])->assertRedirect(route('barbers.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@yhonbarber.com',
            'role' => User::ROLE_BARBER,
        ]);
    }
}
