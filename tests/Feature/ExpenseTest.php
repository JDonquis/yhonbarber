<?php

namespace Tests\Feature;

use App\Models\Closing;
use App\Models\Expense;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
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

    public function test_admin_pages_render(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('expenses.index'))->assertOk();
        $this->actingAs($admin)->get(route('expenses.create'))->assertOk();
    }

    public function test_barber_cannot_access_expenses(): void
    {
        $barber = $this->barber();

        $this->actingAs($barber)->get(route('expenses.index'))->assertForbidden();
        $this->actingAs($barber)->get(route('expenses.create'))->assertForbidden();
        $this->actingAs($barber)->post(route('expenses.store'), [
            'concept' => 'Alquiler',
            'amount_usd' => 50,
            'expense_date' => now()->toDateString(),
        ])->assertForbidden();
    }

    public function test_admin_can_manage_expenses(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('expenses.store'), [
            'concept' => 'Alquiler',
            'amount_usd' => 50,
            'expense_date' => now()->toDateString(),
        ])->assertRedirect(route('expenses.index'));

        $expense = Expense::firstOrFail();
        $this->assertSame('Alquiler', $expense->concept);
        $this->assertSame('50.00', $expense->amount_usd);
        $this->assertSame($admin->id, $expense->user_id);

        $this->actingAs($admin)->get(route('expenses.edit', $expense))->assertOk();

        $this->actingAs($admin)->put(route('expenses.update', $expense), [
            'concept' => 'Alquiler del local',
            'amount_usd' => 60,
            'expense_date' => now()->toDateString(),
        ])->assertRedirect(route('expenses.index'));

        $this->assertSame('60.00', $expense->fresh()->amount_usd);
        $this->assertSame('Alquiler del local', $expense->fresh()->concept);

        $this->actingAs($admin)->delete(route('expenses.destroy', $expense))->assertRedirect();
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_expenses_reduce_the_store_net_earnings_on_the_dashboard(): void
    {
        $this->setting('commission_rate', 40);
        $service = Service::create(['name' => 'Corte', 'price' => 10, 'active' => true]);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ]);

        Expense::create([
            'concept' => 'Electricidad',
            'amount_usd' => 2,
            'expense_date' => now()->toDateString(),
            'user_id' => $admin->id,
        ]);

        // Venta 10 - comisión 4 - gasto 2 = 4
        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('todayShopAmount', 4.0)
            ->assertViewHas('todayExpenses', 2.0);
    }

    public function test_closing_summary_subtracts_expenses_from_the_store_amount(): void
    {
        $this->setting('commission_rate', 40);
        $service = Service::create(['name' => 'Corte', 'price' => 10, 'active' => true]);
        $admin = $this->admin();
        $barber = $this->barber();

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ]);

        Expense::create([
            'concept' => 'Insumos',
            'amount_usd' => 2,
            'expense_date' => now()->toDateString(),
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('closings.generate'), [
            'period_type' => 'diario',
            'date' => now()->toDateString(),
        ])->assertRedirect();

        $closing = Closing::firstOrFail();

        $this->assertSame('2.00', $closing->total_expenses_usd);
        $this->assertSame('4.00', $closing->shop_amount_usd);
        $this->assertSame('Insumos', $closing->details['gastos'][0]['concept']);
    }
}
