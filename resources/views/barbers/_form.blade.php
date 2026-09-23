@php
    $barber = $barber ?? null;
    $editing = (bool) $barber;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">{{ $editing ? 'Editar barbero' : 'Nuevo barbero' }}</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card>
            <form method="POST" action="{{ $editing ? route('barbers.update', $barber) : route('barbers.store') }}" class="p-6 space-y-5">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="name" value="Nombre completo" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $barber?->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="email" value="Correo electrónico" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $barber?->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone" value="Teléfono" />
                        <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $barber?->phone)" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="password" :value="$editing ? 'Nueva contraseña (opcional)' : 'Contraseña'" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! $editing" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="! $editing" />
                    </div>
                </div>

                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $barber?->active ?? true))
                           class="rounded border-slate-300 text-amber-500 shadow-sm focus:ring-amber-500">
                    <span class="text-sm text-slate-600">Activo</span>
                </label>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('barbers.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                    <x-primary-button>{{ $editing ? 'Guardar cambios' : 'Registrar barbero' }}</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
