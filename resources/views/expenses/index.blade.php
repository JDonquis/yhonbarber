@php
    $preserve = array_filter([
        'search' => $search,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Gastos</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4"
         x-data="{
             period: @js($period),
             search: @js($search ?? ''),
             from: @js($from),
             to: @js($to),
             label: @js($periodLabel),
             filteredAmount: @js(usd($stats['filtered'])),
             filteredCount: @js($stats['filteredCount']),
             endpoint: @js(route('expenses.filter')),
             indexUrl: @js(route('expenses.index')),
             loading: false,
             error: '',
             rangeOpen: @js($period === 'rango'),
             controller: null,
             queryParams() {
                 const params = new URLSearchParams();
                 if (this.period) params.set('period', this.period);
                 if (this.search) params.set('search', this.search);
                 if (this.period === 'rango') {
                     params.set('from', this.from);
                     params.set('to', this.to);
                 }
                 return params;
             },
             async apply() {
                 if (this.controller) this.controller.abort();
                 const controller = new AbortController();
                 this.controller = controller;
                 this.loading = true;
                 this.error = '';
                 const params = this.queryParams();
                 try {
                     const response = await fetch(`${this.endpoint}?${params.toString()}`, {
                         cache: 'no-store',
                         headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                         signal: controller.signal,
                     });
                     if (!response.ok) throw new Error('Request failed');
                     const data = await response.json();
                     this.$refs.list.innerHTML = data.html;
                     this.label = data.label;
                     this.filteredAmount = data.filtered;
                     this.filteredCount = data.filtered_count;
                     this.period = data.period;
                     this.from = data.from;
                     this.to = data.to;
                     const finalParams = new URLSearchParams();
                     if (data.period) finalParams.set('period', data.period);
                     if (this.search) finalParams.set('search', this.search);
                     if (data.period === 'rango') {
                         finalParams.set('from', data.from);
                         finalParams.set('to', data.to);
                     }
                     window.history.replaceState({}, '', `${this.indexUrl}?${finalParams.toString()}`);
                 } catch (error) {
                     if (error.name !== 'AbortError') {
                         this.error = 'No se pudieron actualizar los gastos. Intenta de nuevo.';
                     }
                 } finally {
                     if (this.controller === controller) {
                         this.loading = false;
                     }
                 }
             },
             setPeriod(period) {
                 this.period = period;
                 this.rangeOpen = false;
                 this.apply();
             },
             applyRange() {
                 this.period = 'rango';
                 this.rangeOpen = true;
                 this.apply();
             },
         }">
        <!-- Encabezado -->
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-widest text-rose-600">
                    <x-icon name="minus" class="h-4 w-4" /> Egresos del estudio
                </span>
                <h2 class="mt-1 text-xl font-extrabold tracking-tight text-slate-800">Gastos</h2>
                <p class="mt-0.5 text-xs leading-snug text-slate-400">Registra los gastos operativos que se descuentan de la ganancia de la tienda.</p>
            </div>
            <a href="{{ route('expenses.create') }}"
               class="flex h-12 shrink-0 items-center justify-center gap-1.5 rounded-xl bg-amber-500 px-4 text-sm font-bold text-slate-900 shadow-md shadow-amber-500/25 transition hover:bg-amber-400 active:scale-95">
                <x-icon name="plus" class="h-4 w-4" /> Nuevo
            </a>
        </div>

        <!-- Resumen -->
        <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2 flex items-center justify-between rounded-xl bg-rose-50 p-4 shadow-sm ring-1 ring-rose-200">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-500 text-white">
                        <x-icon name="minus" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Gastos · <span x-text="label">{{ $periodLabel }}</span></p>
                        <p class="text-[11px] text-rose-700"><span x-text="filteredCount">{{ $stats['filteredCount'] }}</span> registro(s)</p>
                    </div>
                </div>
                <span class="shrink-0 text-2xl font-extrabold tracking-tight text-rose-700" x-text="filteredAmount">{{ usd($stats['filtered']) }}</span>
            </div>

            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <span class="text-xs text-slate-400">Gastos de hoy</span>
                <p class="mt-1 text-lg font-extrabold tracking-tight text-slate-800">{{ usd($stats['today']) }}</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <span class="text-xs text-slate-400">Gastos del mes</span>
                <p class="mt-1 text-lg font-extrabold tracking-tight text-slate-800">{{ usd($stats['month']) }}</p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="space-y-2">
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                @foreach (['hoy' => 'Hoy', 'semana' => 'Semana', 'mes' => 'Mes', 'all' => 'Todos'] as $key => $label)
                    <a href="{{ route('expenses.index', array_merge($preserve, ['period' => $key])) }}"
                       @click.prevent="setPeriod('{{ $key }}')"
                       class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                       :class="period === '{{ $key }}' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                        {{ $label }}
                    </a>
                @endforeach
                <button type="button" @click="rangeOpen = ! rangeOpen"
                        class="flex h-9 items-center gap-1 whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="rangeOpen || period === 'rango' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-amber-600'">
                    <x-icon name="clock" class="h-4 w-4" /> Rango
                </button>
            </div>

            <form method="GET" action="{{ route('expenses.index') }}" @submit.prevent="apply()">
                <input type="hidden" name="period" value="{{ $period }}" :value="period">
                <input type="hidden" name="from" value="{{ $period === 'rango' ? $from : '' }}" :value="period === 'rango' ? from : ''">
                <input type="hidden" name="to" value="{{ $period === 'rango' ? $to : '' }}" :value="period === 'rango' ? to : ''">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="search" class="h-5 w-5" />
                    </span>
                    <input type="text" name="search" x-model="search" value="{{ $search }}" placeholder="Buscar por concepto o nota..."
                           @input.debounce.400ms="apply()"
                           class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-10 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-amber-500 focus:ring-amber-500" />
                    <button type="button" x-show="search.length" x-cloak @click="search = ''; apply()"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-700" title="Limpiar">
                        <x-icon name="x" class="h-4 w-4" />
                    </button>
                </div>
            </form>

            <form method="GET" action="{{ route('expenses.index') }}" @submit.prevent="applyRange()"
                  x-show="rangeOpen" x-cloak x-transition.opacity
                  class="rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Rango de fechas</span>
                    <span x-show="period === 'rango'" x-cloak class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">Activo</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="expense_from" class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Desde</label>
                        <input id="expense_from" name="from" type="date" x-model="from"
                               class="block h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                    </div>
                    <div>
                        <label for="expense_to" class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Hasta</label>
                        <input id="expense_to" name="to" type="date" x-model="to"
                               class="block h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                    </div>
                </div>
                <div class="mt-2 flex gap-2">
                    <button type="submit" :disabled="loading"
                            class="flex h-10 flex-1 items-center justify-center gap-1.5 rounded-lg bg-amber-500 text-xs font-bold text-slate-900 shadow-sm transition hover:bg-amber-400 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60">
                        <span x-show="! loading" class="flex items-center gap-1.5">
                            <x-icon name="filter" class="h-4 w-4" /> Ver rango
                        </span>
                        <span x-show="loading" x-cloak class="flex items-center gap-1.5">
                            <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-900/30 border-t-slate-900"></span>
                            Actualizando...
                        </span>
                    </button>
                    <button type="button" @click="setPeriod('mes')" :disabled="loading"
                            class="flex h-10 items-center justify-center rounded-lg bg-slate-100 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-200 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60">
                        Mes actual
                    </button>
                </div>
            </form>

            <p x-show="error" x-cloak x-text="error"
               class="rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 ring-1 ring-rose-100"></p>
        </div>

        <!-- Listado -->
        <section class="space-y-2.5">
            <div class="flex items-center justify-between px-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Gastos registrados</span>
                <span class="text-[11px] font-semibold text-amber-600">
                    <span x-show="! loading">Moneda: USD ($)</span>
                    <span x-show="loading" x-cloak>Actualizando...</span>
                </span>
            </div>

            <div x-ref="list" class="transition-opacity" :class="loading ? 'pointer-events-none opacity-50' : ''">
                @include('expenses._list', ['expenses' => $expenses])
            </div>
        </section>
    </div>
</x-app-layout>
