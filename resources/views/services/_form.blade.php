@php
    $service = $service ?? null;
    $editing = (bool) $service;
    $currentPrice = (float) old('price', $service?->price ?? 0);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Tipos de corte</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        entry: {{ $currentPrice }},
        currency: 'USD',
        rate: {{ (float) $currentRate }},
        formatVes(value) {
            return 'Bs. ' + Number(value || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        formatUsd(value) {
            return '$' + Number(value || 0).toFixed(2);
        },
        get usdValue() {
            return this.currency === 'VES' ? Number(this.entry || 0) / this.rate : Number(this.entry || 0);
        },
        get equivalent() {
            return this.currency === 'VES' ? this.formatUsd(this.usdValue) : this.formatVes(this.usdValue * this.rate);
        },
        setCurrency(value) {
            if (value === this.currency) return;
            const usd = this.usdValue;
            this.currency = value;
            this.entry = value === 'VES' ? Number((usd * this.rate).toFixed(2)) : Number(usd.toFixed(2));
        }
    }">
        <!-- Subcabecera: volver + tasa -->
        <div class="flex items-center justify-between">
            <a href="{{ route('services.index') }}"
               class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-100 active:scale-95" title="Volver al catálogo">
                <x-icon name="arrow-left" class="h-5 w-5" />
            </a>
            <div class="flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 shadow-sm ring-1 ring-slate-200">
                <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">BCV:</span>
                <span class="text-[11px] font-bold text-emerald-600">{{ ves($currentRate) }}</span>
            </div>
        </div>

        <!-- Título -->
        <div>
            <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-600">Catálogo · {{ $editing ? 'Edición' : 'Registro' }}</span>
            <h2 class="text-xl font-extrabold tracking-tight text-slate-800">{{ $editing ? 'Editar Tipo de Corte' : 'Nuevo Tipo de Corte' }}</h2>
            <p class="mt-0.5 text-xs text-slate-400">Configuración del servicio, precio en dualidad monetaria y estado.</p>
        </div>

        <form method="POST" action="{{ $editing ? route('services.update', $service) : route('services.store') }}" class="space-y-4">
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
                               value="{{ old('name', $service?->name) }}"
                               placeholder="Ej. Fade Clásico, Corte Degradado, Perfilado..."
                               class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <!-- Descripción -->
                    <div class="space-y-1.5">
                        <x-input-label for="description" value="Descripción" />
                        <textarea id="description" name="description" rows="3"
                                  placeholder="Detalles o notas sobre el servicio..."
                                  class="block w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500">{{ old('description', $service?->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" />
                    </div>

                    <!-- Precio -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <x-input-label for="price" value="Precio *" />
                            <div class="inline-flex rounded-lg bg-slate-100 p-0.5">
                                <button type="button" @click="setCurrency('USD')"
                                        :class="currency === 'USD' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'"
                                        class="rounded-md px-3 py-1 text-xs font-bold uppercase tracking-wide transition">USD</button>
                                <button type="button" @click="setCurrency('VES')"
                                        :class="currency === 'VES' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'"
                                        class="rounded-md px-3 py-1 text-xs font-bold uppercase tracking-wide transition">VES</button>
                            </div>
                        </div>

                        <input type="hidden" name="price" :value="usdValue.toFixed(2)">

                        <div class="relative flex items-center rounded-xl border border-slate-200 bg-slate-50 px-4 transition focus-within:border-amber-500 focus-within:bg-white">
                            <span class="select-none pr-2 text-lg font-extrabold text-amber-500" x-text="currency === 'VES' ? 'Bs.' : '$'"></span>
                            <input id="price" type="number" step="0.01" min="0" required x-ref="priceInput"
                                   x-model.number="entry"
                                   class="h-14 w-full border-0 bg-transparent p-0 text-xl font-extrabold tracking-tight text-slate-800 focus:ring-0" />
                            <button type="button" @click="entry = 0; $refs.priceInput.focus()"
                                    class="p-2 text-slate-400 transition hover:text-slate-700" title="Limpiar precio">
                                <x-icon name="x" class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Equivalencia -->
                        <div class="mt-1 flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-100">
                            <span class="flex items-center gap-1.5 text-xs text-slate-400">
                                <x-icon name="refresh" class="h-4 w-4 text-emerald-500" /> Equivale a:
                            </span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-bold text-emerald-600" x-text="equivalent"></span>
                                <span class="text-[11px] text-slate-400">(a la tasa actual)</span>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('price')" />
                    </div>

                    <!-- Divisor -->
                    <div class="h-px w-full bg-slate-100"></div>

                    <!-- Activo (toggle) -->
                    <div class="flex items-center justify-between py-1">
                        <div class="pr-3">
                            <span class="text-sm font-semibold text-slate-700">Activo</span>
                            <span class="block text-xs text-slate-400">Habilitar este corte en el sistema</span>
                        </div>
                        <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" name="active" value="1" class="peer sr-only" @checked(old('active', $service?->active ?? true))>
                            <span class="relative h-7 w-12 rounded-full bg-slate-200 transition-colors after:absolute after:left-[2px] after:top-[2px] after:h-6 after:w-6 after:rounded-full after:bg-white after:shadow after:transition-all after:content-[''] peer-checked:bg-amber-500 peer-checked:after:translate-x-full"></span>
                        </label>
                    </div>
                </div>
            </x-card>

            <!-- Acciones -->
            <div class="flex flex-col gap-2 pt-1">
                <button type="submit"
                        class="flex h-[54px] w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 text-sm font-extrabold uppercase tracking-wide text-slate-900 shadow-lg shadow-amber-500/30 transition-all hover:opacity-95 active:scale-[0.985]">
                    <x-icon name="check" class="h-5 w-5" />
                    {{ $editing ? 'Guardar cambios' : 'Registrar corte' }}
                </button>
                <a href="{{ route('services.index') }}"
                   class="flex h-12 w-full items-center justify-center rounded-xl bg-white text-sm font-semibold text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-50 active:scale-[0.985]">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
