@php
    $usersData = $users->map(fn ($user) => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'role' => $user->role,
        'active' => (bool) $user->active,
    ])->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Usuarios</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        users: @js($usersData),
        search: '',
        roleFilter: 'all',
        statusFilter: 'all',
        generated: null,
        token: '{{ csrf_token() }}',
        editBase: '{{ url('/users') }}',
        currentUserId: {{ auth()->id() }},
        get filteredUsers() {
            const q = this.search.toLowerCase().trim();
            return this.users.filter((user) => {
                const matchesSearch = ! q
                    || user.name.toLowerCase().includes(q)
                    || (user.email || '').toLowerCase().includes(q)
                    || (user.phone || '').includes(q);
                const matchesRole = this.roleFilter === 'all' || user.role === this.roleFilter;
                const matchesStatus = this.statusFilter === 'all' || (this.statusFilter === 'active' ? user.active : ! user.active);
                return matchesSearch && matchesRole && matchesStatus;
            });
        },
        get adminCount() { return this.users.filter((user) => user.role === 'admin').length; },
        get barberCount() { return this.users.filter((user) => user.role === 'barbero').length; },
        get activeCount() { return this.users.filter((user) => user.active).length; },
        get inactiveCount() { return this.users.filter((user) => ! user.active).length; },
        initials(name) {
            return (name || '').trim().split(/\s+/).slice(0, 2).map((word) => word.charAt(0)).join('').toUpperCase();
        },
        isSelf(user) { return user.id === this.currentUserId; },
        async resetPassword(user) {
            if (! confirm('¿Generar una nueva contraseña temporal para ' + user.name + '?')) return;
            const body = new FormData();
            body.append('_token', this.token);
            const response = await fetch(this.editBase + '/' + user.id + '/reset-password', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: body,
            });
            if (response.ok) {
                this.generated = await response.json();
            }
        },
        copyPassword() {
            if (this.generated && navigator.clipboard) {
                navigator.clipboard.writeText(this.generated.password);
            }
        }
    }">
        <!-- Encabezado -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-amber-600">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-500"></span>
                    Seguridad &amp; Control de Acceso
                </span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">
                    <span x-text="users.length"></span> Registros
                </span>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-800">Cuentas del Sistema</h2>
                    <p class="mt-0.5 text-xs text-slate-400">Administra el acceso, credenciales y privilegios del personal.</p>
                </div>
                <a href="{{ route('users.create') }}"
                   class="flex h-[50px] w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 text-sm font-bold text-slate-900 shadow-lg shadow-amber-500/25 transition hover:bg-amber-400 active:scale-[0.985] sm:w-auto">
                    <x-icon name="plus" class="h-5 w-5" /> Nuevo usuario
                </a>
            </div>
        </div>

        <!-- Búsqueda y filtros (fijos al hacer scroll) -->
        <div class="sticky top-16 z-20 space-y-3 bg-slate-100 py-2">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="search" class="h-5 w-5" />
                </span>
                <input type="text" x-model="search" placeholder="Buscar por nombre, correo o teléfono..."
                       class="h-[50px] w-full rounded-xl border border-slate-200 bg-white pl-11 pr-10 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-amber-500 focus:ring-amber-500" />
                <button type="button" x-show="search.length" x-cloak @click="search = ''"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-700" title="Limpiar">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <!-- Rol -->
            <div class="space-y-1.5">
                <span class="px-0.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Filtrar por rol</span>
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                    <button type="button" @click="roleFilter = 'all'"
                            class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-full px-3 text-xs font-semibold transition active:scale-95"
                            :class="roleFilter === 'all' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                        Todos los roles (<span x-text="users.length"></span>)
                    </button>
                    <button type="button" @click="roleFilter = 'admin'"
                            class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-full px-3 text-xs font-semibold transition active:scale-95"
                            :class="roleFilter === 'admin' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-amber-600 ring-1 ring-slate-200 hover:text-amber-700'">
                        Administradores (<span x-text="adminCount"></span>)
                    </button>
                    <button type="button" @click="roleFilter = 'barbero'"
                            class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-full px-3 text-xs font-semibold transition active:scale-95"
                            :class="roleFilter === 'barbero' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                        Barberos (<span x-text="barberCount"></span>)
                    </button>
                </div>
            </div>

            <!-- Estado -->
            <div class="space-y-1.5">
                <span class="px-0.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Filtrar por estado</span>
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                    <button type="button" @click="statusFilter = 'all'"
                            class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-full px-3 text-xs font-semibold transition active:scale-95"
                            :class="statusFilter === 'all' ? 'bg-slate-800 text-white shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                        Todos los estados
                    </button>
                    <button type="button" @click="statusFilter = 'active'"
                            class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-full px-3 text-xs font-semibold transition active:scale-95"
                            :class="statusFilter === 'active' ? 'bg-slate-800 text-white shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Activos (<span x-text="activeCount"></span>)
                    </button>
                    <button type="button" @click="statusFilter = 'inactive'"
                            class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-full px-3 text-xs font-semibold transition active:scale-95"
                            :class="statusFilter === 'inactive' ? 'bg-slate-800 text-white shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                        <span class="h-2 w-2 rounded-full bg-slate-400"></span> Inactivos (<span x-text="inactiveCount"></span>)
                    </button>
                </div>
            </div>
        </div>

        <!-- Tarjetas -->
        <div class="flex flex-col gap-3">
            <template x-for="user in filteredUsers" :key="user.id">
                <div class="relative flex flex-col gap-3 overflow-hidden rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md"
                     :class="user.active ? '' : 'opacity-80'">
                    <template x-if="isSelf(user)">
                        <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-500 via-amber-400 to-transparent"></span>
                    </template>

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-base font-bold"
                                 :class="user.role === 'admin' ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500'"
                                 x-text="initials(user.name)"></div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <h3 class="truncate text-base font-bold text-slate-800" x-text="user.name"></h3>
                                    <template x-if="isSelf(user)">
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">Tu cuenta</span>
                                    </template>
                                </div>
                                <p class="truncate text-xs text-slate-400" x-text="user.email"></p>
                                <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                      :class="user.role === 'admin' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500'"
                                      x-text="user.role === 'admin' ? 'Administrador' : 'Barbero'"></span>
                            </div>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide"
                              :class="user.active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400'">
                            <span class="h-2 w-2 rounded-full" :class="user.active ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                            <span x-text="user.active ? 'Activo' : 'Inactivo'"></span>
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <x-icon name="phone" class="h-4 w-4 shrink-0 text-amber-500" />
                        <span class="truncate text-xs text-slate-600" x-text="user.phone || 'Sin teléfono'"></span>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <button type="button" @click="resetPassword(user)"
                                class="flex h-[42px] flex-1 items-center justify-center gap-1.5 rounded-lg bg-slate-100 px-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                            <x-icon name="key" class="h-4 w-4 text-amber-500" /> <span class="truncate">Contraseña</span>
                        </button>
                        <a :href="editBase + '/' + user.id + '/edit'"
                           class="flex h-[42px] flex-1 items-center justify-center gap-1.5 rounded-lg bg-slate-100 px-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                            <x-icon name="edit" class="h-4 w-4 text-slate-500" /> Editar
                        </a>
                        <template x-if="! isSelf(user)">
                            <form method="POST" :action="editBase + '/' + user.id" onsubmit="return confirm('¿Eliminar este usuario?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Eliminar"
                                        class="flex h-[42px] items-center justify-center rounded-lg bg-rose-50 px-3 text-rose-500 transition hover:bg-rose-100">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </form>
                        </template>
                        <template x-if="isSelf(user)">
                            <button type="button" disabled title="No puedes eliminar tu propia cuenta"
                                    class="flex h-[42px] cursor-not-allowed items-center justify-center rounded-lg bg-slate-100 px-3 text-slate-300">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Sin resultados -->
            <div x-show="filteredUsers.length === 0" x-cloak
                 class="flex flex-col items-center justify-center rounded-xl bg-white px-6 py-12 text-center shadow-sm ring-1 ring-slate-200">
                <span class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-icon name="users" class="h-8 w-8" />
                </span>
                <p class="mt-3 text-base font-bold text-slate-800">Sin coincidencias</p>
                <p class="mt-1 max-w-xs text-xs text-slate-400">No se encontraron cuentas que coincidan con los filtros o término de búsqueda.</p>
                <button type="button" @click="search = ''; roleFilter = 'all'; statusFilter = 'all'"
                        class="mt-4 rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-amber-600 transition hover:bg-slate-200">
                    Restablecer filtros
                </button>
            </div>
        </div>

        <!-- Políticas de seguridad -->
        <div class="flex items-start gap-3 rounded-xl bg-slate-100 p-4 ring-1 ring-slate-200">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                <x-icon name="lock" class="h-5 w-5" />
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-800">Políticas de seguridad MR Yhon</p>
                <p class="mt-0.5 text-xs leading-relaxed text-slate-500">
                    Las contraseñas temporales generadas se muestran una sola vez. Anótalas y entrégalas al usuario;
                    pueden cambiarla luego desde "Mi perfil".
                </p>
            </div>
        </div>

        <!-- Modal contraseña generada -->
        <div x-show="generated" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="generated = null"></div>
            <div class="relative flex w-full max-w-sm flex-col gap-4 rounded-t-3xl bg-white p-5 shadow-2xl sm:rounded-2xl">
                <div class="text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                        <x-icon name="key" class="h-6 w-6" />
                    </span>
                    <h3 class="mt-3 text-lg font-bold text-slate-800">Nueva contraseña temporal</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Para <span class="font-semibold text-slate-700" x-text="generated ? generated.name : ''"></span>
                    </p>
                </div>
                <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-4">
                    <span class="select-all font-mono text-lg font-bold tracking-widest text-slate-900" x-text="generated ? generated.password : ''"></span>
                    <button type="button" @click="copyPassword()" title="Copiar"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500 ring-1 ring-slate-200 transition hover:text-amber-600">
                        <x-icon name="copy" class="h-4 w-4" />
                    </button>
                </div>
                <p class="text-center text-xs text-amber-600">Por seguridad no se volverá a mostrar.</p>
                <button type="button" @click="generated = null"
                        class="h-12 w-full rounded-xl bg-amber-500 text-sm font-bold text-slate-900 transition hover:bg-amber-400 active:scale-[0.985]">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
