@php
    $closingsData = $closings->map(function ($closing) {
        $services = collect($closing->details['servicios'] ?? []);
        $products = collect($closing->details['productos'] ?? []);
        $total = (float) $closing->total_usd;

        return [
            'id' => $closing->id,
            'period' => $closing->period_type,
            'label' => ucfirst($closing->period_type),
            'closed' => $closing->isClosed(),
            'start' => optional($closing->period_start)->format('d/m/Y'),
            'end' => optional($closing->period_end)->format('d/m/Y'),
            'time' => optional($closing->closed_at ?? $closing->created_at)->format('H:i'),
            'services' => (float) $closing->total_services_usd,
            'products' => (float) $closing->total_products_usd,
            'commission' => (float) $closing->barber_commission_usd,
            'commissionPct' => $total > 0 ? (int) round((float) $closing->barber_commission_usd / $total * 100) : 0,
            'total' => $total,
            'tickets' => (int) $closing->ticket_count,
            'serviceQty' => (int) $services->sum('quantity'),
            'productQty' => (int) $products->sum('quantity'),
            'showUrl' => route('closings.show', $closing),
            'printUrl' => route('closings.print', $closing),
        ];
    })->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Cierres y reportes</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        closings: @js($closingsData),
        search: '',
        filter: 'all',
        open: false,
        get filteredClosings() {
            const q = this.search.toLowerCase().trim();
            return this.closings.filter((closing) => {
                const matchesSearch = ! q
                    || closing.label.toLowerCase().includes(q)
                    || (closing.start || '').includes(q)
                    || (closing.end || '').includes(q);
                const matchesFilter = this.filter === 'all' || closing.period === this.filter;
                return matchesSearch && matchesFilter;
            });
        },
        get dailyCount() { return this.closings.filter((c) => c.period === 'diario').length; },
        get weeklyCount() { return this.closings.filter((c) => c.period === 'semanal').length; },
        get monthlyCount() { return this.closings.filter((c) => c.period === 'mensual').length; },
        periodClass(period) {
            if (period === 'diario') return 'bg-amber-100 text-amber-700';
            if (period === 'semanal') return 'bg-emerald-100 text-emerald-700';
            return 'bg-sky-100 text-sky-700';
        },
        formatUsd(value) {
            return '$ ' + Number(value || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }">
        <!-- Encabezado -->
        <section class="space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-widest text-amber-600">
                        <x-icon name="chart" class="h-4 w-4" /> Finanzas &amp; Auditoría
                    </span>
                    <h2 class="mt-1 text-xl font-extrabold tracking-tight text-slate-800">Cierres y Reportes</h2>
                    <p class="mt-0.5 text-xs leading-snug text-slate-400">Historial de liquidaciones, balances de servicios y comisiones calculadas.</p>
                </div>
                <button type="button" @click="open = ! open"
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-slate-900 shadow-md shadow-amber-500/25 transition hover:bg-amber-400 active:scale-95"
                        title="Generar cierre">
                    <x-icon name="plus" class="h-6 w-6" />
                </button>
            </div>

            <!-- Resumen del mes -->
            <div class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <x-icon name="dollar" class="h-5 w-5" />
                    </span>
                    <div>
                        <span class="block text-xs text-slate-400">Recaudado este mes</span>
                        <span class="text-sm font-bold tracking-tight text-slate-800">{{ usd($monthRevenue) }} USD</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="block text-xs text-slate-400">Comisiones liq.</span>
                    <span class="text-sm font-bold text-amber-600">{{ usd($monthCommission) }} USD</span>
                </div>
            </div>
        </section>

        <!-- Panel generar -->
        <section x-show="open" x-cloak x-transition.opacity
                 class="rounded-xl bg-white p-4 shadow-lg ring-1 ring-slate-200">
            <div class="mb-3 flex items-center justify-between">
                <span class="flex items-center gap-2 text-sm font-bold text-slate-800">
                    <x-icon name="chart" class="h-4 w-4 text-amber-500" /> Generar Cierre
                </span>
                <button type="button" @click="open = false" class="text-slate-400 transition hover:text-slate-700">
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </div>
            <form method="POST" action="{{ route('closings.generate') }}" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label for="period_type" class="text-xs font-medium text-slate-500">Tipo de período</label>
                    <select id="period_type" name="period_type"
                            class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500">
                        <option value="diario">Diario (corte del día)</option>
                        <option value="semanal">Semanal (7 días)</option>
                        <option value="mensual">Mensual (cierre calendario)</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label for="date" class="text-xs font-medium text-slate-500">Fecha de referencia</label>
                    <input id="date" name="date" type="date" required value="{{ old('date', now()->toDateString()) }}"
                           class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                    <x-input-error :messages="$errors->get('date')" />
                </div>
                <button type="submit"
                        class="flex h-[50px] w-full items-center justify-center gap-2 rounded-xl bg-amber-500 text-sm font-bold text-slate-900 shadow-md shadow-amber-500/25 transition hover:bg-amber-400 active:scale-95">
                    <x-icon name="chart" class="h-5 w-5" /> Calcular &amp; auditar cierre
                </button>
            </form>
        </section>

        <!-- Filtros (fijos al hacer scroll) -->
        <section class="sticky top-16 z-20 space-y-2 bg-slate-100 py-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Filtrar por período</span>
                <span class="text-[11px] font-semibold text-amber-600"><span x-text="closings.length"></span> registrados</span>
            </div>
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                <button type="button" @click="filter = 'all'"
                        class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'all' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                    Todos
                </button>
                <button type="button" @click="filter = 'diario'"
                        class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'diario' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                    Diarios (<span x-text="dailyCount"></span>)
                </button>
                <button type="button" @click="filter = 'semanal'"
                        class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'semanal' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                    Semanales (<span x-text="weeklyCount"></span>)
                </button>
                <button type="button" @click="filter = 'mensual'"
                        class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'mensual' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                    Mensuales (<span x-text="monthlyCount"></span>)
                </button>
            </div>

            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="search" class="h-5 w-5" />
                </span>
                <input type="text" x-model="search" placeholder="Buscar por período o fecha (ej. 05/10/2026)..."
                       class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-10 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-amber-500 focus:ring-amber-500" />
                <button type="button" x-show="search.length" x-cloak @click="search = ''"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-700" title="Limpiar">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>
        </section>

        <!-- Cierres -->
        <section class="flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800">Auditorías registradas</h3>
                <span class="text-xs text-slate-400">Moneda: USD ($)</span>
            </div>

            <template x-for="closing in filteredClosings" :key="closing.id">
                <article class="relative flex flex-col gap-3 overflow-hidden rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md">
                    <span class="absolute left-0 top-0 h-full w-1.5"
                          :class="closing.closed ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse'"></span>

                    <!-- Metadatos -->
                    <div class="flex items-center justify-between gap-2 pl-2">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide"
                                  :class="periodClass(closing.period)" x-text="closing.period"></span>
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide"
                                  :class="closing.closed ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                                <span class="h-1.5 w-1.5 rounded-full" :class="closing.closed ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                <span x-text="closing.closed ? 'Cerrado' : 'Abierto'"></span>
                            </span>
                        </div>
                        <span class="flex items-center gap-1 text-xs text-slate-400">
                            <x-icon name="clock" class="h-4 w-4" /> <span x-text="closing.time"></span>
                        </span>
                    </div>

                    <!-- Rango -->
                    <div class="flex items-center gap-1.5 pl-2 text-slate-700">
                        <x-icon name="clock" class="h-4 w-4 text-amber-500" />
                        <span class="text-sm font-semibold tracking-tight"><span x-text="closing.start"></span> — <span x-text="closing.end"></span></span>
                    </div>

                    <!-- Métricas -->
                    <div class="grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3">
                        <div>
                            <span class="text-xs text-slate-400">Servicios</span>
                            <p class="text-sm font-semibold text-slate-700" x-text="formatUsd(closing.services)"></p>
                            <span class="text-[10px] text-slate-400"><span x-text="closing.serviceQty"></span> servicios</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400">Productos</span>
                            <p class="text-sm font-semibold text-slate-700" x-text="formatUsd(closing.products)"></p>
                            <span class="text-[10px] text-slate-400"><span x-text="closing.productQty"></span> unidades</span>
                        </div>
                        <div class="pt-1">
                            <span class="text-xs text-slate-400">Comisión barberos</span>
                            <p class="text-sm font-semibold text-amber-600" x-text="formatUsd(closing.commission)"></p>
                            <span class="text-[10px] text-slate-400">Reparto <span x-text="closing.commissionPct"></span>%</span>
                        </div>
                        <div class="pt-1">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total USD</span>
                            <p class="text-xl font-extrabold leading-tight text-amber-600" x-text="formatUsd(closing.total)"></p>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="flex items-center gap-2 pl-2">
                        <a :href="closing.showUrl"
                           class="flex h-[42px] flex-1 items-center justify-center gap-1.5 rounded-lg bg-slate-100 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                            <x-icon name="search" class="h-4 w-4 text-amber-500" /> Ver detalle
                        </a>
                        <a :href="closing.printUrl" target="_blank"
                           class="flex h-[42px] items-center justify-center gap-1.5 rounded-lg bg-slate-100 px-4 text-xs font-semibold text-slate-500 transition hover:bg-slate-200">
                            <x-icon name="printer" class="h-4 w-4" /> PDF
                        </a>
                    </div>
                </article>
            </template>

            <!-- Sin resultados -->
            <div x-show="filteredClosings.length === 0" x-cloak
                 class="flex flex-col items-center justify-center rounded-xl bg-white px-6 py-12 text-center shadow-sm ring-1 ring-slate-200">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-icon name="chart" class="h-7 w-7" />
                </span>
                <p class="mt-3 text-base font-bold text-slate-800">No hay cierres</p>
                <p class="mt-1 max-w-xs text-xs text-slate-400">Genera un cierre con el botón + o ajusta los filtros.</p>
                <button type="button" @click="search = ''; filter = 'all'"
                        class="mt-4 rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-amber-600 transition hover:bg-slate-200">
                    Restablecer filtros
                </button>
            </div>
        </section>
    </div>
</x-app-layout>
