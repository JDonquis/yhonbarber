<?php

namespace Tests\Feature;

use App\Models\PasswordResetRequest;
use App\Models\Sale;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN, 'active' => true]);
    }

    private function barber(): User
    {
        return User::factory()->create(['role' => User::ROLE_BARBER, 'active' => true]);
    }

    public function test_admin_can_view_user_pages(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('users.index'))->assertOk();
        $this->actingAs($admin)->get(route('users.create'))->assertOk();
        $this->actingAs($admin)->get(route('users.edit', $admin))->assertOk();
        $this->actingAs($admin)->get(route('password-requests.index'))->assertOk();
    }

    public function test_barber_cannot_manage_users(): void
    {
        $barber = $this->barber();

        $this->actingAs($barber)->get(route('users.index'))->assertForbidden();
        $this->actingAs($barber)->get(route('password-requests.index'))->assertForbidden();
    }

    public function test_admin_can_create_a_user(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Nuevo Admin',
            'email' => 'nuevo@yhonbarber.com',
            'phone' => '0414-1111111',
            'role' => User::ROLE_ADMIN,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'active' => '1',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@yhonbarber.com',
            'role' => User::ROLE_ADMIN,
            'active' => true,
        ]);
    }

    public function test_admin_can_update_a_user(): void
    {
        $admin = $this->admin();
        $barber = $this->barber();

        $this->actingAs($admin)->put(route('users.update', $barber), [
            'name' => 'Barbero Renombrado',
            'email' => $barber->email,
            'role' => User::ROLE_BARBER,
            'active' => '1',
        ])->assertRedirect(route('users.index'));

        $this->assertSame('Barbero Renombrado', $barber->fresh()->name);
    }

    public function test_admin_can_reset_a_password_and_receives_it_once(): void
    {
        $admin = $this->admin();
        $barber = $this->barber();

        $response = $this->actingAs($admin)->post(route('users.reset-password', $barber));

        $response->assertRedirect(route('users.index'))->assertSessionHas('generated_password');

        $password = $response->getSession()->get('generated_password');

        $this->assertTrue(Hash::check($password, $barber->fresh()->password));
    }

    public function test_admin_can_reset_a_password_via_json(): void
    {
        $admin = $this->admin();
        $barber = $this->barber();

        $response = $this->actingAs($admin)
            ->post(route('users.reset-password', $barber), [], ['Accept' => 'application/json']);

        $response->assertOk()->assertJsonStructure(['password', 'name']);

        $this->assertTrue(Hash::check($response->json('password'), $barber->fresh()->password));
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete(route('users.destroy', $admin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_last_active_admin_cannot_be_demoted(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => User::ROLE_BARBER,
            'active' => '1',
        ])->assertSessionHas('error');

        $this->assertSame(User::ROLE_ADMIN, $admin->fresh()->role);
    }

    public function test_admin_can_resolve_a_password_reset_request(): void
    {
        $admin = $this->admin();
        $barber = $this->barber();

        $resetRequest = PasswordResetRequest::create([
            'user_id' => $barber->id,
            'status' => PasswordResetRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)->post(route('password-requests.resolve', $resetRequest));

        $response->assertRedirect(route('password-requests.index'))->assertSessionHas('generated_password');

        $password = $response->getSession()->get('generated_password');

        $this->assertTrue(Hash::check($password, $barber->fresh()->password));
        $this->assertSame(PasswordResetRequest::STATUS_RESOLVED, $resetRequest->fresh()->status);
        $this->assertSame($admin->id, $resetRequest->fresh()->resolved_by);
    }

    public function test_admin_can_resolve_a_password_reset_request_via_json(): void
    {
        $admin = $this->admin();
        $barber = $this->barber();

        $resetRequest = PasswordResetRequest::create([
            'user_id' => $barber->id,
            'status' => PasswordResetRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('password-requests.resolve', $resetRequest), [], ['Accept' => 'application/json']);

        $response->assertOk()->assertJsonStructure(['password', 'name']);

        $this->assertTrue(Hash::check($response->json('password'), $barber->fresh()->password));
        $this->assertSame(PasswordResetRequest::STATUS_RESOLVED, $resetRequest->fresh()->status);
    }

    public function test_deleting_a_user_is_soft_and_keeps_their_sales(): void
    {
        $admin = $this->admin();
        $barber = $this->barber();

        Setting::updateOrCreate(['key' => 'commission_rate'], ['value' => '40']);
        $service = Service::create(['name' => 'Corte clásico', 'price' => 10, 'active' => true]);

        $this->actingAs($admin)->post(route('sales.store-service'), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'payment_currency' => 'USD',
        ]);

        $sale = Sale::firstOrFail();

        $this->actingAs($admin)->delete(route('users.destroy', $barber))
            ->assertRedirect(route('users.index'));

        $this->assertSoftDeleted('users', ['id' => $barber->id]);
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'barber_id' => $barber->id]);
        $this->assertSame($barber->name, $sale->fresh()->barber->name);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['active' => false]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deleting_a_user_frees_their_email(): void
    {
        $admin = $this->admin();
        $barber = $this->barber();
        $originalEmail = $barber->email;

        $this->actingAs($admin)->delete(route('users.destroy', $barber))
            ->assertRedirect(route('users.index'));

        $this->assertSoftDeleted('users', ['id' => $barber->id]);
        $this->assertDatabaseHas('users', ['id' => $barber->id, 'deleted_email' => $originalEmail]);
        $this->assertNotSame($originalEmail, $barber->fresh()->email);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Nuevo Barbero',
            'email' => $originalEmail,
            'role' => User::ROLE_BARBER,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'active' => '1',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['email' => $originalEmail, 'deleted_at' => null]);
    }
}
