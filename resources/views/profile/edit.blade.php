@php
    $user = $user ?? auth()->user();
    $words = preg_split('/\s+/', trim($user->name));
    $initials = mb_strtoupper(mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1));
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Mi perfil</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4">
        <!-- Encabezado -->
        <div>
            <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-widest text-amber-600">
                <x-icon name="user" class="h-4 w-4" /> Configuración de usuario
            </span>
            <h2 class="mt-1 text-xl font-extrabold tracking-tight text-slate-800">Mi Perfil</h2>
            <p class="mt-0.5 text-xs text-slate-400">Administra tu información personal, seguridad y credenciales del estudio.</p>
        </div>

        <!-- Hero de perfil -->
        <section class="relative overflow-hidden rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="pointer-events-none absolute -top-12 -right-12 h-40 w-40 rounded-full bg-amber-500/10 blur-3xl"></div>
            <div class="relative z-10 flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                <div class="relative shrink-0">
                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-amber-100 text-3xl font-extrabold text-amber-600 shadow-[0_0_24px_rgba(245,158,11,0.25)]">
                        {{ $initials }}
                    </div>
                    <span class="absolute bottom-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-white ring-2 ring-white">
                        <x-icon name="check" class="h-3 w-3" />
                    </span>
                </div>

                <div class="min-w-0 flex-1 text-center sm:text-left">
                    <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                        <h2 class="truncate text-lg font-bold text-slate-800">{{ $user->name }}</h2>
                        <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">
                            {{ $user->isAdmin() ? 'Administrador' : 'Barbero' }}
                        </span>
                    </div>
                    <div class="mt-1 flex flex-wrap items-center justify-center gap-1.5 text-xs text-slate-400 sm:justify-start">
                        <x-icon name="check" class="h-4 w-4 text-emerald-500" />
                        <span class="font-medium text-emerald-600">{{ $user->isAdmin() ? 'Administrador del estudio' : 'Barbero del estudio' }}</span>
                        <span>•</span>
                        <span>ID #{{ $user->id }}</span>
                    </div>
                    <div class="mt-3 space-y-1.5 rounded-lg bg-slate-50 p-2.5">
                        <div class="flex items-center gap-2 text-xs">
                            <x-icon name="mail" class="h-4 w-4 shrink-0 text-amber-500" />
                            <span class="truncate text-slate-700">{{ $user->email }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <x-icon name="phone" class="h-4 w-4 shrink-0 text-emerald-500" />
                            <span class="truncate text-slate-700">{{ $user->phone ?: 'Sin teléfono registrado' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Datos de la cuenta -->
        <x-card title="Datos de la cuenta" description="Actualiza tu nombre, correo y teléfono de contacto">
            <div class="p-5">
                @include('profile.partials.update-profile-information-form')
            </div>
        </x-card>

        <!-- Seguridad -->
        <x-card title="Seguridad" description="Cambia la contraseña de acceso a tu cuenta">
            <div class="p-5">
                @include('profile.partials.update-password-form')
            </div>
        </x-card>

        <!-- Zona de peligro -->
        @include('profile.partials.delete-user-form')
    </div>

    <script>
        (function () {
            var key = 'scroll:' + window.location.pathname;
            var saved = sessionStorage.getItem(key);

            if (saved === null) {
                document.addEventListener('submit', function () {
                    sessionStorage.setItem(key, window.scrollY);
                });
                return;
            }

            sessionStorage.removeItem(key);
            var y = parseInt(saved, 10) || 0;
            var restore = function () { window.scrollTo(0, y); };
            document.addEventListener('DOMContentLoaded', restore);
            window.addEventListener('load', restore);
        })();
    </script>
</x-app-layout>
