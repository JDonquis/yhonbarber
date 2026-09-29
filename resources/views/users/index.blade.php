<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Usuarios</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-5">
        @if (session('generated_password'))
            <div class="rounded-xl border border-amber-300 bg-amber-50 px-5 py-4">
                <p class="text-sm font-semibold text-amber-900">Nueva contraseña temporal</p>
                <p class="mt-1 text-sm text-amber-800">
                    Usuario: <span class="font-medium">{{ session('generated_for') }}</span>
                </p>
                <p class="mt-2 text-sm text-amber-800">
                    Contraseña:
                    <span class="ml-1 select-all rounded-md bg-white px-2 py-1 font-mono text-base font-bold tracking-wider text-slate-900">{{ session('generated_password') }}</span>
                </p>
                <p class="mt-2 text-xs text-amber-700">Por seguridad no se volverá a mostrar. El usuario puede cambiarla en "Mi perfil".</p>
            </div>
        @endif

        <x-card title="Cuentas del sistema" description="Administra el acceso, los roles y las contraseñas">
            <x-slot name="actions">
                <a href="{{ route('users.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">
                    <x-icon name="plus" class="h-4 w-4" /> Nuevo usuario
                </a>
            </x-slot>

            <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-end gap-3 border-b border-slate-100 px-5 py-4">
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-xs font-medium text-slate-500">Buscar</label>
                    <x-text-input id="search" name="search" class="mt-1 block w-full" :value="request('search')" placeholder="Nombre o correo" />
                </div>
                <div>
                    <label for="role" class="block text-xs font-medium text-slate-500">Rol</label>
                    <select id="role" name="role"
                            class="mt-1 block rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Todos</option>
                        <option value="{{ \App\Models\User::ROLE_ADMIN }}" @selected(request('role') === \App\Models\User::ROLE_ADMIN)>Administrador</option>
                        <option value="{{ \App\Models\User::ROLE_BARBER }}" @selected(request('role') === \App\Models\User::ROLE_BARBER)>Barbero</option>
                    </select>
                </div>
                <button class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Filtrar
                </button>
                @if (request()->filled('search') || request()->filled('role'))
                    <a href="{{ route('users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Limpiar</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Nombre</th>
                            <th class="px-5 py-3 font-medium">Correo</th>
                            <th class="px-5 py-3 font-medium">Teléfono</th>
                            <th class="px-5 py-3 font-medium">Rol</th>
                            <th class="px-5 py-3 font-medium text-center">Estado</th>
                            <th class="px-5 py-3 font-medium text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-700">
                                    {{ $user->name }}
                                    @if ($user->id === auth()->id())
                                        <span class="ml-1 text-xs font-normal text-slate-400">(tú)</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-500">{{ $user->email }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $user->phone ?: '—' }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :tone="$user->isAdmin() ? 'amber' : 'sky'">
                                        {{ $user->isAdmin() ? 'Administrador' : 'Barbero' }}
                                    </x-badge>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <x-badge :tone="$user->active ? 'green' : 'red'">{{ $user->active ? 'Activo' : 'Inactivo' }}</x-badge>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('users.reset-password', $user) }}"
                                              onsubmit="return confirm('¿Generar una nueva contraseña temporal para {{ $user->name }}?');">
                                            @csrf
                                            <button class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100" title="Restablecer contraseña">
                                                <x-icon name="key" class="h-4 w-4" />
                                            </button>
                                        </form>
                                        <a href="{{ route('users.edit', $user) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100" title="Editar">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('¿Eliminar este usuario?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50" title="Eliminar">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">No hay usuarios que coincidan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <div>{{ $users->links() }}</div>
    </div>
</x-app-layout>
