<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class BarberController extends Controller
{
    public function index()
    {
        $barbers = User::query()->barbers()->orderBy('name')->paginate(15);

        return view('barbers.index', compact('barbers'));
    }

    public function create()
    {
        return view('barbers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_BARBER,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('barbers.index')->with('status', 'Barbero registrado.');
    }

    public function edit(User $barber)
    {
        return view('barbers.edit', compact('barber'));
    }

    public function update(Request $request, User $barber)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($barber->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'active' => ['nullable', 'boolean'],
        ]);

        $barber->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'active' => $request->boolean('active'),
        ]);

        if (! empty($data['password'])) {
            $barber->password = Hash::make($data['password']);
        }

        $barber->save();

        return redirect()->route('barbers.index')->with('status', 'Barbero actualizado.');
    }

    public function destroy(Request $request, User $barber)
    {
        if ($barber->id === $request->user()->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $barber->delete();

        return redirect()->route('barbers.index')->with('status', 'Barbero eliminado.');
    }
}
