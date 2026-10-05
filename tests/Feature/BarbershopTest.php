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
        $this->actingAs($barber)->get(route('sales.create-product'))->assertForbidden();
        $this->actingAs($barber)->post(route('sales.store-product'), [])->assertForbidden();
    }

    public function test_barber_can_open_the_register_service_form(): void
    {
        $this->actingAs($this->barber())
            ->get(route('sales.create-service'))
            ->assertOk();
    }

    public function test_barber_can_view_dashboard(): void
    {
        $this->actingAs($this->barber())
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_admin_can_view_a_sale_detail(): void
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

        $sale = Sale::firstOrFail();

        $this->actingAs($admin)
            ->get(route('sales.show', $sale))
            ->assertOk()
            ->assertSee($sale->code)
            ->assertSee('Información de la operación');

        $this->actingAs($admin)
            ->get(route('sales.print', $sale))
            ->assertOk()
            ->assertSee('COMPROBANTE')
            ->assertSee($sale->code);
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

    public function test_service_sale_can_use_a_custom_price(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'price' => 20,
            'payment_currency' => 'USD',
        ])->assertRedirect();

        $sale = Sale::firstOrFail();

        $this->assertSame('20.00', $sale->total_usd);
        $this->assertSame('8.00', $sale->barber_commission_usd);
        $this->assertSame('20.00', $sale->items->first()->unit_price_usd);
        $this->assertSame('10.00', $service->fresh()->price);
    }

    public function test_admin_can_save_a_custom_price_as_the_new_default(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'price' => 12.5,
            'update_service_price' => '1',
            'payment_currency' => 'USD',
        ])->assertRedirect();

        $this->assertSame('12.50', $service->fresh()->price);
    }

    public function test_barber_cannot_change_the_configured_service_price(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $barber = $this->barber();

        $this->actingAs($barber)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'price' => 25,
            'update_service_price' => '1',
            'payment_currency' => 'USD',
        ])->assertRedirect();

        $this->assertSame('25.00', Sale::firstOrFail()->total_usd);
        $this->assertSame('10.00', $service->fresh()->price);
    }

    public function test_sales_history_can_be_filtered(): void
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

        $sale = Sale::firstOrFail();

        $this->actingAs($admin)
            ->get(route('sales.index', ['period' => 'hoy', 'type' => 'servicio']))
            ->assertOk()
            ->assertSee($sale->code);

        $this->actingAs($admin)
            ->get(route('sales.index', ['type' => 'producto']))
            ->assertOk()
            ->assertDontSee($sale->code);

        $this->actingAs($admin)
            ->get(route('sales.index', ['search' => 'no-existe-xyz']))
            ->assertOk()
            ->assertDontSee($sale->code);
    }

    public function test_sales_history_defaults_to_today_and_can_show_all(): void
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
        $today = Sale::firstOrFail();

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ]);
        $yesterday = Sale::orderByDesc('id')->firstOrFail();
        $yesterday->update(['sold_at' => now()->subDay()]);

        $this->actingAs($admin)->get(route('sales.index'))
            ->assertOk()
            ->assertSee($today->code)
            ->assertDontSee($yesterday->code);

        $this->actingAs($admin)->get(route('sales.index', ['period' => 'all']))
            ->assertOk()
            ->assertSee($today->code)
            ->assertSee($yesterday->code);
    }

    public function test_sales_history_search_matches_item_names(): void
    {
        $product = Product::create(['name' => 'Talco Perfumado', 'price' => 5, 'stock' => 10, 'active' => true]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('sales.store-product'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_currency' => 'USD',
        ]);

        $sale = Sale::firstOrFail();

        $this->actingAs($admin)
            ->get(route('sales.index', ['period' => 'all', 'search' => 'Talco']))
            ->assertOk()
            ->assertSee($sale->code);
    }

    public function test_filter_links_reset_pagination(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        for ($i = 0; $i < 20; $i++) {
            $this->actingAs($admin)->post(route('sales.store-service'), [
                'service_id' => $service->id,
                'barber_id' => $barber->id,
                'payment_currency' => 'USD',
            ]);
        }

        $response = $this->actingAs($admin)->get(route('sales.index', ['period' => 'all', 'page' => 2]));

        $response->assertOk();

        $this->assertStringContainsString(
            route('sales.index', ['period' => 'hoy']),
            $response->getContent(),
        );

        $this->assertStringNotContainsString(
            route('sales.index', ['period' => 'hoy', 'page' => 2]),
            $response->getContent(),
        );
    }

    public function test_admin_can_toggle_service_active(): void
    {
        $service = $this->service(10);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('services.toggle', $service))->assertRedirect();
        $this->assertFalse($service->fresh()->active);

        $this->actingAs($admin)
            ->post(route('services.toggle', $service), [], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['active' => true]);

        $this->assertTrue($service->fresh()->active);
    }

    public function test_admin_can_update_service_price(): void
    {
        $service = $this->service(10);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('services.update-price', $service), ['price' => 15.5], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['price' => 15.5]);

        $this->assertSame('15.50', $service->fresh()->price);
    }

    public function test_service_with_sales_cannot_be_deleted(): void
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

        $this->actingAs($admin)->delete(route('services.destroy', $service))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('services', ['id' => $service->id]);

        $empty = Service::create(['name' => 'Servicio sin ventas', 'price' => 5, 'active' => true]);

        $this->actingAs($admin)->delete(route('services.destroy', $empty))
            ->assertRedirect(route('services.index'));

        $this->assertDatabaseMissing('services', ['id' => $empty->id]);
    }

    public function test_admin_can_toggle_barber_active(): void
    {
        $barber = $this->barber();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('barbers.toggle', $barber))->assertRedirect();
        $this->assertFalse($barber->fresh()->active);

        $this->actingAs($admin)
            ->post(route('barbers.toggle', $barber), [], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['active' => true]);

        $this->assertTrue($barber->fresh()->active);
    }

    public function test_admin_can_register_stock_movement_via_json(): void
    {
        $product = Product::create(['name' => 'Cera', 'price' => 5, 'stock' => 10, 'active' => true]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('products.stock.store', $product), ['type' => 'entrada', 'quantity' => 5], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['stock' => 15]);

        $this->assertSame(15, $product->fresh()->stock);

        $this->actingAs($admin)
            ->post(route('products.stock.store', $product), ['type' => 'salida', 'quantity' => 99], ['Accept' => 'application/json'])
            ->assertStatus(422);

        $this->assertSame(15, $product->fresh()->stock);
    }

    public function test_product_with_sales_cannot_be_deleted(): void
    {
        $product = Product::create(['name' => 'Cera', 'price' => 5, 'stock' => 10, 'active' => true]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('sales.store-product'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payment_currency' => 'USD',
        ]);

        $this->actingAs($admin)->delete(route('products.destroy', $product))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->id]);

        $free = Product::create(['name' => 'Producto libre', 'price' => 1, 'stock' => 1, 'active' => true]);

        $this->actingAs($admin)->delete(route('products.destroy', $free))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $free->id]);
    }

    public function test_product_with_only_movements_can_be_deleted(): void
    {
        $product = Product::create(['name' => 'Gel', 'price' => 5, 'stock' => 10, 'active' => true]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('products.stock.store', $product), [
            'type' => 'entrada',
            'quantity' => 5,
        ]);

        $this->actingAs($admin)->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_can_toggle_product_active(): void
    {
        $product = Product::create(['name' => 'Talco', 'price' => 5, 'stock' => 10, 'active' => true]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('products.toggle', $product))->assertRedirect();
        $this->assertFalse($product->fresh()->active);

        $this->actingAs($admin)
            ->post(route('products.toggle', $product), [], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['active' => true]);

        $this->assertTrue($product->fresh()->active);
    }

    public function test_admin_can_view_a_closing_report(): void
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

        $this->actingAs($admin)
            ->get(route('closings.show', $closing))
            ->assertOk()
            ->assertSee('Resumen del período');

        $this->actingAs($admin)
            ->get(route('closings.print', $closing))
            ->assertOk()
            ->assertSee('logo.jpeg')
            ->assertSee('Bs cobrados');
    }

    public function test_closing_stores_historical_and_reference_bolivars(): void
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
        Sale::orderBy('id')->firstOrFail()->update(['exchange_rate' => 30, 'total_ves' => 300]);

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ]);
        Sale::orderByDesc('id')->firstOrFail()->update(['exchange_rate' => 40, 'total_ves' => 400]);

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'diario',
            'date' => now()->toDateString(),
        ]);

        $closing = Closing::firstOrFail();

        // Bs históricos: 300 + 400
        $this->assertSame('700.00', $closing->total_ves);
        // Tasa promedio ponderada: 700 / 20
        $this->assertSame('35.0000', $closing->average_rate);
        // Referencia: 20 USD * tasa de cierre (30 en tests)
        $this->assertSame('600.00', $closing->total_ves_reference);
        $this->assertSame('30.0000', $closing->exchange_rate);
    }

    public function test_barber_with_sales_cannot_be_deleted(): void
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

        $this->actingAs($admin)->delete(route('barbers.destroy', $barber))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $barber->id, 'deleted_at' => null]);
    }

    public function test_barber_without_sales_can_be_deleted(): void
    {
        $admin = $this->admin();
        $barber = $this->barber();

        $this->actingAs($admin)->delete(route('barbers.destroy', $barber))
            ->assertRedirect(route('barbers.index'));

        $this->assertSoftDeleted('users', ['id' => $barber->id]);
    }

    public function test_dashboard_and_history_show_store_net_earnings(): void
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

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ganancia de la tienda');

        $this->actingAs($admin)->get(route('sales.index', ['period' => 'all']))
            ->assertOk()
            ->assertSee('Ganancia neta');
    }
}
