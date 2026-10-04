<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(string $id, string $email): SocialiteUser
    {
        $user = new SocialiteUser();
        $user->id = $id;
        $user->email = $email;
        $user->name = 'Google User';

        return $user;
    }

    private function mockProvider(?SocialiteUser $googleUser = null): void
    {
        $provider = Mockery::mock(Provider::class);

        if ($googleUser) {
            $provider->shouldReceive('user')->andReturn($googleUser);
        }

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_disabled_when_not_configured(): void
    {
        $this->get(route('auth.google'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');
    }

    public function test_redirects_to_google_when_configured(): void
    {
        config([
            'services.google.client_id' => 'client-id',
            'services.google.client_secret' => 'secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_existing_user_is_logged_in_and_linked(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@mryhonbarber.com',
            'role' => User::ROLE_ADMIN,
            'active' => true,
            'google_id' => null,
        ]);

        $this->mockProvider($this->fakeGoogleUser('google-123', $user->email));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame('google-123', $user->fresh()->google_id);
    }

    public function test_google_id_match_works_even_if_email_changes(): void
    {
        $user = User::factory()->create([
            'email' => 'viejo@mryhonbarber.com',
            'role' => User::ROLE_BARBER,
            'active' => true,
            'google_id' => 'google-abc',
        ]);

        $this->mockProvider($this->fakeGoogleUser('google-abc', 'nuevo@mryhonbarber.com'));

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_unknown_email_is_rejected(): void
    {
        $this->mockProvider($this->fakeGoogleUser('google-999', 'desconocido@example.com'));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_inactive_user_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'inactivo@mryhonbarber.com',
            'active' => false,
        ]);

        $this->mockProvider($this->fakeGoogleUser('google-555', $user->email));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }
}
