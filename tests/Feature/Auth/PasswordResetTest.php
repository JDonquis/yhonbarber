<?php

namespace Tests\Feature\Auth;

use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_request_screen_can_be_rendered(): void
    {
        $this->get('/forgot-password')->assertStatus(200);
    }

    public function test_requesting_a_reset_creates_a_pending_request(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_requests', [
            'user_id' => $user->id,
            'status' => PasswordResetRequest::STATUS_PENDING,
        ]);
    }

    public function test_an_unknown_email_does_not_create_a_request(): void
    {
        $this->post('/forgot-password', ['email' => 'nadie@example.com'])
            ->assertSessionHas('status');

        $this->assertDatabaseCount('password_reset_requests', 0);
    }

    public function test_an_inactive_user_does_not_create_a_request(): void
    {
        $user = User::factory()->create(['active' => false]);

        $this->post('/forgot-password', ['email' => $user->email]);

        $this->assertDatabaseCount('password_reset_requests', 0);
    }
}
