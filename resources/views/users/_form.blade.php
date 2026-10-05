@php
    $user = $user ?? null;
    $editing = (bool) $user;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Usuarios</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4">
        <!-- Subcabecera: volver -->
        <div class="flex items-center justify-between">
            <a href="{{ route('users.index') }}"
               class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-100 active:scale-95" title="Volver a usuarios">
                <x-icon name="arrow-left" class="h-5 w-5" />
            </a>
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                <x-icon name="user" class="h-5 w-5" />
            </span>
        </div>

        <!-- Título -->
        <div>
            <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-600">Seguridad · {{ $editing ? 'Edición' : 'Registro' }}</span>
            <h2 class="text-xl font-extrabold tracking-tight text-slate-800">{{ $editing ? 'Editar Usuario' : 'Nuevo Usuario' }}</h2>
            <p class="mt-0.5 text-xs text-slate-400">Credenciales, rol y permisos de acceso al sistema.</p>
        </div>

        <form method="POST" action="{{ $editing ? route('users.update', $user) : route('users.store') }}" class="space-y-4">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-card>
                <div class="space-y-5 p-4 sm:p-5">
                    <!-- Nombre -->
                    <div class="space-y-1.5">
                        <x-input-label for="name" value="Nombre completo *" />
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <x-icon name="user" class="h-4 w-4" />
                            </span>
                            <input id="name" name="name" type="text" required autofocus
                                   value="{{ old('name', $user?->name) }}"
                                   placeholder="Ej. Juan Donquis"
                                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                        </div>
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <!-- Email -->
                        <div class="space-y-1.5">
                            <x-input-label for="email" value="Correo electrónico *" />
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <x-icon name="mail" class="h-4 w-4" />
                                </span>
                                <input id="email" name="email" type="email" required
                                       value="{{ old('email', $user?->email) }}"
                                       placeholder="usuario@yhonbarber.com"
                                       class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                            </div>
                            <x-input-error :messages="$errors->get('email')" />
                        </div>

                        <!-- Teléfono -->
                        <div class="space-y-1.5">
                            <x-input-label for="phone" value="Teléfono" />
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <x-icon name="phone" class="h-4 w-4" />
                                </span>
                                <input id="phone" name="phone" type="tel"
                                       value="{{ old('phone', $user?->phone) }}"
                                       placeholder="+58 412 000 0000"
                                       class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                            </div>
                            <x-input-error :messages="$errors->get('phone')" />
                        </div>
                    </div>

                    <!-- Rol -->
                    <div class="space-y-1.5">
                        <x-input-label for="role" value="Rol *" />
                        <select id="role" name="role"
                                class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500">
                            <option value="{{ \App\Models\User::ROLE_BARBER }}" @selected(old('role', $user?->role ?? \App\Models\User::ROLE_BARBER) === \App\Models\User::ROLE_BARBER)>Barbero</option>
                            <option value="{{ \App\Models\User::ROLE_ADMIN }}" @selected(old('role', $user?->role) === \App\Models\User::ROLE_ADMIN)>Administrador</option>
                        </select>
                        <p class="text-xs text-slate-400">Los administradores gestionan catálogo, usuarios, cierres y configuración.</p>
                        <x-input-error :messages="$errors->get('role')" />
                    </div>

                    <!-- Estado (toggle) -->
                    <div class="flex items-center justify-between py-1">
                        <div class="pr-3">
                            <span class="text-sm font-semibold text-slate-700">Activo</span>
                            <span class="block text-xs text-slate-400">Permite que el usuario inicie sesión</span>
                        </div>
                        <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" name="active" value="1" class="peer sr-only" @checked(old('active', $user?->active ?? true))>
                            <span class="relative h-7 w-12 rounded-full bg-slate-200 transition-colors after:absolute after:left-[2px] after:top-[2px] after:h-6 after:w-6 after:rounded-full after:bg-white after:shadow after:transition-all after:content-[''] peer-checked:bg-amber-500 peer-checked:after:translate-x-full"></span>
                        </label>
                    </div>

                    <div class="h-px w-full bg-slate-100"></div>

                    <!-- Contraseña -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <x-input-label for="password" :value="$editing ? 'Nueva contraseña (opcional)' : 'Contraseña *'" />
                            <input id="password" name="password" type="password" autocomplete="new-password" @required(! $editing)
                                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                            <x-input-error :messages="$errors->get('password')" />
                        </div>
                        <div class="space-y-1.5">
                            <x-input-label for="password_confirmation" value="Confirmar contraseña" />
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $editing)
                                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">Mínimo 8 caracteres. El sistema no envía correos: anota la contraseña y entrégasela al usuario.</p>
                </div>
            </x-card>

            <!-- Acciones -->
            <div class="flex flex-col gap-2 pt-1">
                <button type="submit"
                        class="flex h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 text-sm font-extrabold uppercase tracking-wide text-slate-900 shadow-lg shadow-amber-500/30 transition-all hover:opacity-95 active:scale-[0.985]">
                    <x-icon name="check" class="h-5 w-5" />
                    {{ $editing ? 'Guardar cambios' : 'Registrar usuario' }}
                </button>
                <a href="{{ route('users.index') }}"
                   class="flex h-12 w-full items-center justify-center rounded-xl bg-white text-sm font-semibold text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-50 active:scale-[0.985]">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
