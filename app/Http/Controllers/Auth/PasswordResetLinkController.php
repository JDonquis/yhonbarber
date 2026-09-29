<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Register a password reset request for the administrator to handle.
     *
     * This application does not send emails, so instead of emailing a reset
     * link we record a request that an administrator resolves manually.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()->where('email', $request->input('email'))->first();

        if ($user && $user->active) {
            PasswordResetRequest::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'status' => PasswordResetRequest::STATUS_PENDING,
                ],
                [],
            );
        }

        return back()->with('status', 'Solicitud registrada. Un administrador te asignará una nueva contraseña.');
    }
}
