@php
    $product = $product ?? null;
    $editing = (bool) $product;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Productos</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        cost: {{ (float) old('cost', $product?->cost ?? 0) }},
        price: {{ (float) old('price', $product?->price ?? 0) }},
        rate: {{ (float) $currentRate }},
        get margin() { return (this.price || 0) - (this.cost || 0); },
        get marginPct() {
            if ((this.cost || 0) > 0) return ((this.price - this.cost) / this.cost) * 100;
            return (this.price || 0) > 0 ? 100 : 0;
        },
        formatVes(value) {
            return 'Bs. ' + Number(value || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }">
        <!-- Subcabecera: volver -->
        <div class="flex items-center justify-between">
            <a href="{{ route('products.index') }}"
               class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-100 active:scale-95" title="Volver a productos">
                <x-icon name="arrow-left" class="h-5 w-5" />
            </a>
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                <x-icon name="box" class="h-5 w-5" />
            </span>
        </div>

        <!-- Título -->
        <div>
            <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-600">Inventario · {{ $editing ? 'Edición' : 'Registro' }}</span>
            <h2 class="text-xl font-extrabold tracking-tight text-slate-800">{{ $editing ? 'Editar Producto' : 'Nuevo Producto' }}</h2>
            <p class="mt-0.5 text-xs text-slate-400">Datos del producto, costos, precios y control de existencias.</p>
        </div>

        <form method="POST" action="{{ $editing ? route('products.update', $product) : route('products.store') }}" class="space-y-4">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-card>
                <div class="space-y-5 p-4 sm:p-5">
                    <!-- Nombre -->
                    <div class="space-y-1.5">
                        <x-input-label for="name" value="Nombre *" />
                        <input id="name" name="name" type="text" required autofocus
                               value="{{ old('name', $product?->name) }}"
                               placeholder="Ej. Cera para cabello, Aceite para barba..."
                               class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <!-- SKU -->
                        <div class="space-y-1.5">
                            <x-input-label for="sku" value="Código / SKU" />
                            <input id="sku" name="sku" type="text"
                                   value="{{ old('sku', $product?->sku) }}"
                                   placeholder="Ej. CER-001"
                                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 font-mono text-sm tracking-wider text-slate-700 placeholder:font-sans placeholder:tracking-normal placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                            <x-input-error :messages="$errors->get('sku')" />
                        </div>

                        <!-- Costo -->
                        <div class="space-y-1.5">
                            <x-input-label for="cost" value="Costo (USD)" />
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-slate-400">$</span>
                                <input id="cost" name="cost" type="number" step="0.01" min="0" x-model.number="cost"
                                       class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-8 pr-4 text-sm text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                            </div>
                            <x-input-error :messages="$errors->get('cost')" />
                        </div>

                        <!-- Precio -->
                        <div class="space-y-1.5">
                            <x-input-label for="price" value="Precio de venta (USD) *" />
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-amber-500">$</span>
                                <input id="price" name="price" type="number" step="0.01" min="0" required x-model.number="price"
                                       class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-8 pr-4 text-sm font-semibold text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                            </div>
                            <p class="text-xs text-slate-400">≈ <span x-text="formatVes((price || 0) * rate)"></span></p>
                            <x-input-error :messages="$errors->get('price')" />
                        </div>

                        <!-- Stock -->
                        <div class="space-y-1.5">
                            <x-input-label for="stock" value="Stock actual *" />
                            <input id="stock" name="stock" type="number" min="0" required
                                   value="{{ old('stock', $product?->stock ?? 0) }}"
                                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                            <x-input-error :messages="$errors->get('stock')" />
                        </div>

                        <!-- Stock mínimo -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <x-input-label for="min_stock" value="Stock mínimo" />
                            <input id="min_stock" name="min_stock" type="number" min="0"
                                   value="{{ old('min_stock', $product?->min_stock ?? 0) }}"
                                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                            <p class="text-xs text-slate-400">Se te avisará en el catálogo cuando el stock llegue a este nivel.</p>
                            <x-input-error :messages="$errors->get('min_stock')" />
                        </div>
                    </div>

                    <!-- Margen en vivo -->
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
                        <span class="text-xs text-slate-400">Margen estimado</span>
                        <div class="text-right">
                            <span class="text-sm font-bold text-emerald-600" x-text="'+$' + margin.toFixed(2)"></span>
                            <span class="ml-1 text-xs font-semibold text-emerald-600" x-text="'+' + marginPct.toFixed(1) + '%'"></span>
                        </div>
                    </div>

                    <div class="h-px w-full bg-slate-100"></div>

                    <!-- Estado (toggle) -->
                    <div class="flex items-center justify-between py-1">
                        <div class="pr-3">
                            <span class="text-sm font-semibold text-slate-700">Activo</span>
                            <span class="block text-xs text-slate-400">Disponible para la venta en el catálogo</span>
                        </div>
                        <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" name="active" value="1" class="peer sr-only" @checked(old('active', $product?->active ?? true))>
                            <span class="relative h-7 w-12 rounded-full bg-slate-200 transition-colors after:absolute after:left-[2px] after:top-[2px] after:h-6 after:w-6 after:rounded-full after:bg-white after:shadow after:transition-all after:content-[''] peer-checked:bg-amber-500 peer-checked:after:translate-x-full"></span>
                        </label>
                    </div>
                </div>
            </x-card>

            <!-- Acciones -->
            <div class="flex flex-col gap-2 pt-1">
                <button type="submit"
                        class="flex h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 text-sm font-extrabold uppercase tracking-wide text-slate-900 shadow-lg shadow-amber-500/30 transition-all hover:opacity-95 active:scale-[0.985]">
                    <x-icon name="check" class="h-5 w-5" />
                    {{ $editing ? 'Guardar cambios' : 'Registrar producto' }}
                </button>
                <a href="{{ route('products.index') }}"
                   class="flex h-12 w-full items-center justify-center rounded-xl bg-white text-sm font-semibold text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-50 active:scale-[0.985]">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
