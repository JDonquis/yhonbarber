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

    public function test_admin_can_generate_a_closing_for_a_custom_date_range(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->registerSale($admin, $service, $barber);
        Sale::query()->latest('id')->firstOrFail()->update(['sold_at' => now()->subDays(10)]);

        $this->registerSale($admin, $service, $barber);

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'rango',
            'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->toDateString(),
        ])->assertRedirect();

        $closing = Closing::query()->where('period_type', 'rango')->firstOrFail();

        $this->assertSame(now()->subDays(2)->toDateString(), $closing->period_start->toDateString());
        $this->assertSame(now()->toDateString(), $closing->period_end->toDateString());
        $this->assertSame('10.00', $closing->total_usd);
        $this->assertSame(1, $closing->ticket_count);

        $this->actingAs($admin)->get(route('closings.show', $closing))->assertOk();
        $this->actingAs($admin)->get(route('closings.print', $closing))->assertOk();
        $this->actingAs($admin)->get(route('closings.index'))->assertOk()->assertSee('Rangos');
    }

    public function test_custom_range_requires_end_after_start(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'rango',
            'start_date' => now()->toDateString(),
            'end_date' => now()->subDays(2)->toDateString(),
        ])->assertSessionHasErrors('end_date');
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

    private function barberBalance($response, User $barber): float
    {
        $row = $response->viewData('barberBalances')->firstWhere('id', $barber->id);

        return (float) ($row->pending_usd ?? 0);
    }

    public function test_dashboard_shows_unsettled_commissions_per_barber(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->registerSale($admin, $service, $barber);

        $response = $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Saldos por pagar');

        $this->assertSame(4.0, $this->barberBalance($response, $barber));
        $this->assertSame(4.0, (float) $response->viewData('totalOwed'));
    }

    public function test_a_closed_general_closing_settles_all_barber_commissions(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->registerSale($admin, $service, $barber);

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'diario',
            'date' => now()->toDateString(),
        ])->assertRedirect();

        $closing = Closing::query()->whereNull('barber_id')->firstOrFail();
        $this->actingAs($admin)->post(route('closings.close', $closing), [])->assertRedirect();

        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $this->assertSame(0.0, $this->barberBalance($response, $barber));
        $this->assertSame(0.0, (float) $response->viewData('totalOwed'));
    }

    public function test_a_closed_barber_liquidation_only_settles_that_barber(): void
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
        $this->actingAs($admin)->post(route('closings.close', $closing), [])->assertRedirect();

        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $this->assertSame(0.0, $this->barberBalance($response, $barber));
        $this->assertSame(4.0, $this->barberBalance($response, $other));
    }

    public function test_an_open_closing_does_not_settle_commissions(): void
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
        ])->assertRedirect();

        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $this->assertSame(4.0, $this->barberBalance($response, $barber));
    }

    public function test_reopening_a_closing_restores_the_pending_balance(): void
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
        ])->assertRedirect();

        $closing = Closing::query()->where('barber_id', $barber->id)->firstOrFail();
        $this->actingAs($admin)->post(route('closings.close', $closing), [])->assertRedirect();
        $this->actingAs($admin)->post(route('closings.reopen', $closing), [])->assertRedirect();

        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $this->assertSame(4.0, $this->barberBalance($response, $barber));
    }

    public function test_a_sale_registered_after_closing_a_liquidation_remains_pending(): void
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
        ])->assertRedirect();

        $closing = Closing::query()->where('barber_id', $barber->id)->firstOrFail();
        $this->actingAs($admin)->post(route('closings.close', $closing), [])->assertRedirect();

        $this->registerSale($admin, $service, $barber);

        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $this->assertSame(4.0, $this->barberBalance($response, $barber));
    }

    public function test_overlapping_closings_keep_a_sale_settled_until_all_are_reopened(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->registerSale($admin, $service, $barber);

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'diario',
            'date' => now()->toDateString(),
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'diario',
            'date' => now()->toDateString(),
            'barber_id' => $barber->id,
        ])->assertRedirect();

        $general = Closing::query()->whereNull('barber_id')->firstOrFail();
        $barberClosing = Closing::query()->where('barber_id', $barber->id)->firstOrFail();

        $this->actingAs($admin)->post(route('closings.close', $general), [])->assertRedirect();
        $this->actingAs($admin)->post(route('closings.close', $barberClosing), [])->assertRedirect();

        $this->actingAs($admin)->post(route('closings.reopen', $general), [])->assertRedirect();

        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->assertSame(0.0, $this->barberBalance($response, $barber));

        $this->actingAs($admin)->post(route('closings.reopen', $barberClosing), [])->assertRedirect();

        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->assertSame(4.0, $this->barberBalance($response, $barber));
    }

    public function test_barber_dashboard_shows_their_own_pending_balance(): void
    {
        $this->setting('commission_rate', 40);
        $service = $this->service(10);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->registerSale($admin, $service, $barber);

        $response = $this->actingAs($barber)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pendiente por cobrar');

        $this->assertSame(4.0, (float) $response->viewData('myPending'));
    }

    public function test_admin_can_delete_a_closing_and_restore_the_pending_balance(): void
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
        ])->assertRedirect();

        $closing = Closing::query()->where('barber_id', $barber->id)->firstOrFail();
        $this->actingAs($admin)->post(route('closings.close', $closing), [])->assertRedirect();

        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->assertSame(0.0, $this->barberBalance($response, $barber));

        $this->actingAs($admin)->delete(route('closings.destroy', $closing))
            ->assertRedirect(route('closings.index'));

        $this->assertDatabaseMissing('closings', ['id' => $closing->id]);

        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->assertSame(4.0, $this->barberBalance($response, $barber));
    }

    public function test_barber_cannot_delete_a_closing(): void
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
        ])->assertRedirect();

        $closing = Closing::query()->where('barber_id', $barber->id)->firstOrFail();

        $this->actingAs($barber)->delete(route('closings.destroy', $closing))
            ->assertForbidden();

        $this->assertDatabaseHas('closings', ['id' => $closing->id]);
    }
}
