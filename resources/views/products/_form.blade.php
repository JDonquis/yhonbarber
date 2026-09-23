@php
    $product = $product ?? null;
    $editing = (bool) $product;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">{{ $editing ? 'Editar producto' : 'Nuevo producto' }}</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <x-card>
            <form method="POST" action="{{ $editing ? route('products.update', $product) : route('products.store') }}" class="p-6 space-y-5">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <x-input-label for="name" value="Nombre" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $product?->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="sku" value="Código / SKU" />
                        <x-text-input id="sku" name="sku" class="mt-1 block w-full" :value="old('sku', $product?->sku)" />
                        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="cost" value="Costo (USD)" />
                        <x-text-input id="cost" name="cost" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('cost', $product?->cost ?? 0)" />
                        <x-input-error :messages="$errors->get('cost')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="price" value="Precio de venta (USD)" />
                        <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price', $product?->price)" required />
                        <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="stock" value="Stock actual" />
                        <x-text-input id="stock" name="stock" type="number" min="0" class="mt-1 block w-full" :value="old('stock', $product?->stock ?? 0)" required />
                        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="min_stock" value="Stock mínimo" />
                        <x-text-input id="min_stock" name="min_stock" type="number" min="0" class="mt-1 block w-full" :value="old('min_stock', $product?->min_stock ?? 0)" />
                        <x-input-error :messages="$errors->get('min_stock')" class="mt-2" />
                    </div>
                </div>

                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $product?->active ?? true))
                           class="rounded border-slate-300 text-amber-500 shadow-sm focus:ring-amber-500">
                    <span class="text-sm text-slate-600">Activo</span>
                </label>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                    <x-primary-button>{{ $editing ? 'Guardar cambios' : 'Registrar producto' }}</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
