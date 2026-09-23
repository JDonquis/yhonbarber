@php
    $service = $service ?? null;
    $editing = (bool) $service;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">{{ $editing ? 'Editar tipo de corte' : 'Nuevo tipo de corte' }}</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card>
            <form method="POST" action="{{ $editing ? route('services.update', $service) : route('services.store') }}" class="p-6 space-y-5">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="name" value="Nombre" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $service?->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" value="Descripción" />
                    <x-text-input id="description" name="description" class="mt-1 block w-full" :value="old('description', $service?->description)" />
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="price" value="Precio (USD)" />
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">$</span>
                        <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="block w-full pl-7" :value="old('price', $service?->price)" required />
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Equivale a {{ ves(to_ves(old('price', $service?->price ?? 0))) }} a la tasa actual.</p>
                    <x-input-error :messages="$errors->get('price')" class="mt-2" />
                </div>

                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $service?->active ?? true))
                           class="rounded border-slate-300 text-amber-500 shadow-sm focus:ring-amber-500">
                    <span class="text-sm text-slate-600">Activo</span>
                </label>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('services.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                    <x-primary-button>{{ $editing ? 'Guardar cambios' : 'Registrar corte' }}</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
