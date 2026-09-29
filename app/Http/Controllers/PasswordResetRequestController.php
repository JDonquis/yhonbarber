<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetRequestController extends Controller
{
    public function index()
    {
        $requests = PasswordResetRequest::query()
            ->with('user')
            ->pending()
            ->latest()
            ->paginate(20);

        $resolved = PasswordResetRequest::query()
            ->with(['user', 'resolvedBy'])
            ->resolved()
            ->latest('resolved_at')
            ->limit(10)
            ->get();

        return view('password-requests.index', compact('requests', 'resolved'));
    }

    public function resolve(Request $request, PasswordResetRequest $passwordResetRequest)
    {
        $user = $passwordResetRequest->user;

        if (! $user) {
            $passwordResetRequest->update([
                'status' => PasswordResetRequest::STATUS_RESOLVED,
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
            ]);

            return redirect()->route('password-requests.index')
                ->with('error', 'El usuario de esta solicitud ya no existe.');
        }

        $password = Str::password(10, symbols: false);

        $user->update(['password' => Hash::make($password)]);

        $passwordResetRequest->update([
            'status' => PasswordResetRequest::STATUS_RESOLVED,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return redirect()->route('password-requests.index')
            ->with('status', 'Contraseña restablecida. Cópiala y entrégala al usuario.')
            ->with('generated_password', $password)
            ->with('generated_for', $user->name.' ('.$user->email.')');
    }

    public function destroy(Request $request, PasswordResetRequest $passwordResetRequest)
    {
        $passwordResetRequest->update([
            'status' => PasswordResetRequest::STATUS_RESOLVED,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return redirect()->route('password-requests.index')
            ->with('status', 'Solicitud descartada.');
    }
}
