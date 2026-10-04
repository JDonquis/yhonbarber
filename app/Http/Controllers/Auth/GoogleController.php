<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id')) {
            return redirect()->route('login')
                ->with('error', 'El acceso con Google no está configurado.');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google and log them in.
     *
     * Only accounts previously created by an administrator can sign in.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')
                ->with('error', 'No se pudo completar el acceso con Google. Inténtalo de nuevo.');
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('login')
                ->with('error', 'Tu cuenta de Google no proporcionó un correo electrónico.');
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'No existe una cuenta autorizada con ese correo. Contacta al administrador.');
        }

        if (! $user->active) {
            return redirect()->route('login')
                ->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        if ($user->google_id !== $googleUser->getId()) {
            $user->forceFill(['google_id' => $googleUser->getId()])->save();
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
