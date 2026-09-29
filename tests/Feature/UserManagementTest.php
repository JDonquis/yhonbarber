<?php

namespace Tests\Feature;

use App\Models\PasswordResetRequest;
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
}
