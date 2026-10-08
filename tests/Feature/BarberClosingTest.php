<?php

namespace Tests\Feature;

use App\Models\Closing;
use App\Models\Sale;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarberClosingTest extends TestCase
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

    private function registerSale(User $admin, Service $service, User $barber): void
    {
        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ])->assertRedirect();
    }

    public function test_admin_can_generate_a_barber_closing_for_a_period(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();
        $other = $this->barber();

        $this->registerSale($admin, $service, $barber);
        $this->registerSale($admin, $service, $other);

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'diario',
            'date' => now()->toDateString(),
            'barber_id' => $barber->id,
        ])->assertRedirect();

        $closing = Closing::query()->where('barber_id', $barber->id)->firstOrFail();

        $this->assertSame('10.00', $closing->total_usd);
        $this->assertSame('4.00', $closing->barber_commission_usd);
        $this->assertSame('6.00', $closing->shop_amount_usd);
        $this->assertSame('0.00', $closing->total_expenses_usd);
        $this->assertSame(1, $closing->ticket_count);
    }

    public function test_barber_closing_only_includes_that_barbers_sales(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();
        $other = $this->barber();

        $this->registerSale($admin, $service, $barber);

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $other->id,
            'payment_currency' => 'USD',
        ]);

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'mensual',
            'date' => now()->toDateString(),
            'barber_id' => $other->id,
        ]);

        $closing = Closing::query()->where('barber_id', $other->id)->firstOrFail();

        $this->assertSame('10.00', $closing->total_usd);
        $this->assertSame(1, $closing->ticket_count);
    }

    public function test_a_closed_barber_liquidation_does_not_block_new_sales(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->registerSale($admin, $service, $barber);

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'diario',
            'date' => now()->toDateString(),
            'barber_id' => $barber->id,
        ]);

        $closing = Closing::query()->where('barber_id', $barber->id)->firstOrFail();
        $this->actingAs($admin)->post(route('closings.close', $closing), [])->assertRedirect();
        $this->assertTrue($closing->fresh()->isClosed());

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ])->assertSessionMissing('error');

        $this->assertSame(2, Sale::count());
    }

    public function test_barber_closing_pages_render(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->registerSale($admin, $service, $barber);

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'diario',
            'date' => now()->toDateString(),
            'barber_id' => $barber->id,
        ]);

        $closing = Closing::query()->where('barber_id', $barber->id)->firstOrFail();

        $this->actingAs($admin)->get(route('closings.show', $closing))
            ->assertOk()
            ->assertSee($barber->name)
            ->assertSee('a pagar');

        $this->actingAs($admin)->get(route('closings.print', $closing))
            ->assertOk()
            ->assertSee($barber->name);

        $this->actingAs($admin)->get(route('closings.index'))
            ->assertOk()
            ->assertSee('Barberos');
    }
}
