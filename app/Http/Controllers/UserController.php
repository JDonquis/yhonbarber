<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->input('role')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->input('search').'%';
                $query->where(fn ($query) => $query
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_BARBER])],
            'password' => ['required', 'confirmed', Password::min(8)],
            'active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('users.index')->with('status', 'Usuario registrado.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_BARBER])],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'active' => ['nullable', 'boolean'],
        ]);

        $active = $request->boolean('active');

        if ($this->wouldRemoveLastAdmin($user, $data['role'], $active)) {
            return back()->withInput()->with('error', 'Debe existir al menos un administrador activo.');
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'active' => $active,
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('status', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($user->isAdmin() && $user->active && ! $this->otherActiveAdminsExist($user)) {
            return back()->with('error', 'Debe existir al menos un administrador activo.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'Usuario eliminado.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $password = Str::password(10, symbols: false);

        $user->update(['password' => Hash::make($password)]);

        return redirect()->route('users.index')
            ->with('status', 'Contraseña restablecida. Cópiala y entrégala al usuario.')
            ->with('generated_password', $password)
            ->with('generated_for', $user->name.' ('.$user->email.')');
    }

    protected function otherActiveAdminsExist(User $user): bool
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->where('active', true)
            ->whereKeyNot($user->getKey())
            ->exists();
    }

    protected function wouldRemoveLastAdmin(User $user, string $role, bool $active): bool
    {
        if (! $user->isAdmin() || ! $user->active) {
            return false;
        }

        $stillAdmin = $role === User::ROLE_ADMIN && $active;

        return ! $stillAdmin && ! $this->otherActiveAdminsExist($user);
    }
}
