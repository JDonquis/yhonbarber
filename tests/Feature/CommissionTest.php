<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function barber(?float $commission = null): User
    {
        return User::factory()->create([
            'role' => User::ROLE_BARBER,
            'commission_rate' => $commission,
        ]);
    }

    private function setting(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsPayload(array $overrides = []): array
    {
        return array_merge([
            'shop_name' => 'YhonBarber',
            'commission_rate' => 40,
            'dolar_api_source' => 'oficial',
        ], $overrides);
    }

    public function test_settings_page_shows_the_commission_section(): void
    {
        $admin = $this->admin();
        $barber = $this->barber(25);

        $this->actingAs($admin)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('Comisiones por barbero')
            ->assertSee($barber->name)
            ->assertSee('Personalizada');
    }

    public function test_barber_commission_equal_to_the_base_is_shown_as_base(): void
    {
        $this->setting('commission_rate', 40);
        $admin = $this->admin();
        $barber = $this->barber(40);

        $this->actingAs($admin)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertSee($barber->name)
            ->assertSee('Base')
            ->assertDontSee('Personalizada');
    }

    public function test_register_service_page_renders_with_barber_rates(): void
    {
        $this->setting('commission_rate', 40);

        $admin = $this->admin();
        Service::create(['name' => 'Corte', 'price' => 10, 'active' => true]);
        $this->barber(25);

        $this->actingAs($admin)
            ->get(route('sales.create-service'))
            ->assertOk()
            ->assertSee('Desglose de comisión');
    }

    public function test_admin_can_set_and_clear_a_barber_commission_from_settings(): void
    {
        $admin = $this->admin();
        $barber = $this->barber();

        $this->actingAs($admin)->put(route('settings.update'), $this->settingsPayload([
            'barber_id' => $barber->id,
            'barber_commission_rate' => 30,
        ]))->assertRedirect(route('settings.edit'));

        $this->assertSame('30.00', $barber->fresh()->commission_rate);

        $this->actingAs($admin)->put(route('settings.update'), $this->settingsPayload([
            'barber_id' => $barber->id,
            'barber_use_base' => 1,
        ]))->assertRedirect(route('settings.edit'));

        $this->assertNull($barber->fresh()->commission_rate);
    }

    public function test_barber_commission_overrides_the_global_rate_on_sales(): void
    {
        $this->setting('commission_rate', 40);
        $service = Service::create(['name' => 'Corte', 'price' => 10, 'active' => true]);
        $admin = $this->admin();
        $barber = $this->barber(25);

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ])->assertRedirect();

        $sale = Sale::firstOrFail();

        $this->assertSame('25.00', $sale->commission_rate);
        $this->assertSame('2.50', $sale->barber_commission_usd);
        $this->assertSame('7.50', $sale->shop_amount_usd);
    }

    public function test_global_rate_still_applies_without_an_override(): void
    {
        $this->setting('commission_rate', 40);
        $service = Service::create(['name' => 'Corte', 'price' => 10, 'active' => true]);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ])->assertRedirect();

        $sale = Sale::firstOrFail();

        $this->assertSame('40.00', $sale->commission_rate);
        $this->assertSame('4.00', $sale->barber_commission_usd);
    }

    public function test_barber_cannot_change_commissions(): void
    {
        $barber = $this->barber();

        $this->actingAs($barber)->put(route('settings.update'), $this->settingsPayload([
            'barber_id' => $barber->id,
            'barber_commission_rate' => 100,
        ]))->assertForbidden();
    }
}
