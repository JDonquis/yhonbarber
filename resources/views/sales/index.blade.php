<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Historial de ventas</h1>
    </x-slot>

    @php
        $methodIcon = function (?string $method) {
            $m = mb_strtolower((string) $method);
            return match (true) {
                $method === null, $method === '' => 'dollar',
                str_contains($m, 'móvil'), str_contains($m, 'movil') => 'phone',
                str_contains($m, 'punto'), str_contains($m, 'débito'), str_contains($m, 'debito'), str_contains($m, 'tarjeta') => 'card',
                str_contains($m, 'transfer'), str_contains($m, 'zelle') => 'refresh',
                default => 'dollar',
            };
        };

        $selectedType = request('type');
        $isProduct = $selectedType === \App\Models\Sale::TYPE_PRODUCT;
        $isService = $selectedType === \App\Models\Sale::TYPE_SERVICE;

        $kpiUsd = (float) ($isProduct ? $summary->product_usd : ($isService ? $summary->service_usd : $summary->total_usd));
        $kpiVes = (float) ($isProduct ? $summary->product_ves : ($isService ? $summary->service_ves : $summary->total_ves));
        $kpiCount = (int) ($isProduct ? $summary->product_count : ($isService ? $summary->service_count : $summary->count));
        $avgTicket = $kpiCount > 0 ? $kpiUsd / $kpiCount : 0;

        $q = function (array $overrides = []) {
            $query = array_merge(request()->query(), $overrides);
            unset($query['page']);

            return route('sales.index', array_filter(
                $query,
                fn ($value) => $value !== null && $value !== ''
            ));
        };

        $activePeriod = $period ?: (($from || $to) ? 'range' : 'hoy');

        $periodLabel = match ($activePeriod) {
            'hoy' => 'Hoy',
            'ayer' => 'Ayer',
            'semana' => 'Esta semana',
            'mes' => 'Este mes',
            'range' => 'Rango seleccionado',
            default => 'Todas',
        };

        $rangeActive = $activePeriod === 'range';
    @endphp

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        query: @js(request('search', '')),
        searching: false,
        async runSearch() {
            const url = new URL(window.location.href);
            const term = (this.query || '').trim();
            if (term) { url.searchParams.set('search', term); } else { url.searchParams.delete('search'); }
            url.searchParams.delete('page');
            this.searching = true;
            try {
                const response = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' } });
                const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                ['sales-summary', 'sales-filters', 'sales-results'].forEach((id) => {
                    const next = doc.getElementById(id);
                    const current = document.getElementById(id);
                    if (next && current) current.innerHTML = next.innerHTML;
                });
                window.history.replaceState({}, '', url.toString());
            } finally {
                this.searching = false;
            }
        }
    }">
        <!-- Encabezado -->
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-lg font-bold text-slate-800">Historial de Ventas</p>
                <span class="text-xs text-slate-400">Registro en tiempo real</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ request()->fullUrl() }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-500 shadow-sm ring-1 ring-slate-200 transition hover:text-amber-600" title="Actualizar">
                    <x-icon name="refresh" class="h-5 w-5" />
                </a>
                <a href="{{ route('dashboard') }}" class="flex h-10 items-center gap-1.5 rounded-xl bg-white px-3 text-sm font-semibold text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">
                    <x-icon name="home" class="h-4 w-4 text-amber-500" />
                    <span class="hidden sm:inline">Panel</span>
                </a>
            </div>
        </div>

        <!-- Tasa + turno -->
        <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                </span>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tasa BCV:</span>
                <span class="text-xs font-bold text-slate-700">{{ ves($currentRate) }} / $1</span>
            </div>
            <span class="flex items-center gap-1 text-xs text-slate-400">
                <x-icon name="clock" class="h-3.5 w-3.5 text-amber-500" /> Turno activo
            </span>
        </div>

        <!-- Accesos rápidos -->
        <div class="grid {{ auth()->user()->isAdmin() ? 'grid-cols-2' : 'grid-cols-1' }} gap-3">
            <a href="{{ route('sales.create-service') }}"
               class="flex h-[50px] items-center justify-center gap-2 rounded-xl bg-amber-500 px-3 text-sm font-bold text-slate-900 shadow-md shadow-amber-500/25 transition hover:bg-amber-400 active:scale-95">
                <x-icon name="scissors" class="h-5 w-5" />
                <span class="truncate">+ Registrar corte</span>
            </a>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('sales.create-product') }}"
                   class="flex h-[50px] items-center justify-center gap-2 rounded-xl bg-slate-900 px-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800 active:scale-95">
                    <x-icon name="bag" class="h-5 w-5 text-amber-400" />
                    <span class="truncate">+ Venta retail</span>
                </a>
            @endif
        </div>

        <!-- KPIs -->
        <div id="sales-summary" class="grid grid-cols-2 gap-3">
            <div class="col-span-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $periodLabel }} · Ventas brutas</span>
                    <span class="flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-600">
                        <x-icon name="check" class="h-3 w-3" /> {{ $kpiCount }} trans.
                    </span>
                </div>
                <div class="mt-2 flex flex-wrap items-baseline justify-between gap-2">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl font-extrabold tracking-tight text-amber-600">{{ usd($kpiUsd) }}</span>
                        <span class="text-xs text-slate-400">USD</span>
                    </div>
                    <div class="text-right">
                        <span class="block text-sm font-semibold text-slate-700">{{ ves($kpiVes) }}</span>
                        <span class="block text-[11px] text-slate-400">Ticket medio {{ usd($avgTicket) }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-3.5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <x-icon name="scissors" class="h-4 w-4" />
                    </span>
                    <span class="text-xs font-medium text-slate-400">{{ (int) $summary->service_count }} cortes</span>
                </div>
                <span class="mt-2 block text-xs text-slate-400">Servicios</span>
                <span class="text-base font-bold text-slate-800">{{ usd($summary->service_usd) }}</span>
            </div>

            <div class="rounded-xl bg-white p-3.5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <x-icon name="bag" class="h-4 w-4" />
                    </span>
                    <span class="text-xs font-medium text-slate-400">{{ (int) $summary->product_count }} arts.</span>
                </div>
                <span class="mt-2 block text-xs text-slate-400">Retail</span>
                <span class="text-base font-bold text-slate-800">{{ usd($summary->product_usd) }}</span>
            </div>
        </div>

        <!-- Buscador y filtros (fijos al hacer scroll) -->
        <div class="sticky top-16 z-20 space-y-3 bg-slate-100 pb-2 pt-2">
        <!-- Búsqueda -->
        <form method="GET" action="{{ route('sales.index') }}">
            @foreach (['type', 'barber_id', 'period'] as $key)
                @if (request()->filled($key))
                    <input type="hidden" name="{{ $key }}" value="{{ request($key) }}">
                @endif
            @endforeach
            @if ($from)
                <input type="hidden" name="from" value="{{ $from }}">
            @endif
            @if ($to)
                <input type="hidden" name="to" value="{{ $to }}">
            @endif

            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="search" class="h-5 w-5" />
                </span>
                <input type="text" name="search" x-model="query" @input.debounce.400ms="runSearch()" placeholder="Buscar por código, barbero, nota..."
                       class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-12 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-amber-500 focus:ring-amber-500" />
                <span x-show="searching" x-cloak class="absolute inset-y-0 right-10 flex items-center text-amber-500">
                    <x-icon name="refresh" class="h-4 w-4 animate-spin" />
                </span>
                <button type="button" onclick="document.getElementById('sales-range-form').classList.toggle('hidden')"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-amber-600" title="Filtros">
                    <x-icon name="filter" class="h-5 w-5" />
                </button>
            </div>
        </form>

        <!-- Rango de fechas -->
        <form method="GET" action="{{ route('sales.index') }}" id="sales-range-form"
              class="grid grid-cols-2 gap-3 rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 @unless ($rangeActive) hidden @endunless">
            <input type="hidden" name="search" :value="query">
            @foreach (['type', 'barber_id'] as $key)
                @if (request()->filled($key))
                    <input type="hidden" name="{{ $key }}" value="{{ request($key) }}">
                @endif
            @endforeach
            <div>
                <label for="from" class="text-xs font-medium text-slate-500">Desde</label>
                <input type="date" id="from" name="from" value="{{ $from }}"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" />
            </div>
            <div>
                <label for="to" class="text-xs font-medium text-slate-500">Hasta</label>
                <input type="date" id="to" name="to" value="{{ $to }}"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" />
            </div>
            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">
                <x-icon name="check" class="h-4 w-4" /> Aplicar
            </button>
            <a href="{{ $q(['from' => null, 'to' => null, 'period' => null]) }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Limpiar</a>
        </form>

        <!-- Filtros rápidos -->
        <div id="sales-filters" class="space-y-2.5">
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5">
                @foreach (['hoy' => 'Hoy', 'ayer' => 'Ayer', 'semana' => 'Esta semana', 'mes' => 'Este mes'] as $key => $label)
                    @php($isActive = $activePeriod === $key)
                    <a href="{{ $isActive ? $q(['period' => 'all', 'from' => null, 'to' => null]) : $q(['period' => $key, 'from' => null, 'to' => null]) }}"
                       class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95
                              {{ $isActive ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
                <button type="button" onclick="document.getElementById('sales-range-form').classList.remove('hidden')"
                        class="flex h-8 items-center gap-1 whitespace-nowrap rounded-full px-3 text-xs font-semibold transition active:scale-95
                               {{ $rangeActive ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-amber-600' }}">
                    <x-icon name="clock" class="h-4 w-4" /> Rango
                </button>
            </div>

            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5">
                <a href="{{ $q(['type' => null]) }}"
                   class="flex h-7 items-center gap-1.5 whitespace-nowrap rounded-lg px-2.5 text-[11px] font-semibold
                          {{ ! $selectedType ? 'bg-slate-800 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ ! $selectedType ? 'bg-amber-400' : 'bg-slate-300' }}"></span>
                    Todos ({{ (int) $summary->count }})
                </a>
                <a href="{{ $q(['type' => \App\Models\Sale::TYPE_SERVICE]) }}"
                   class="flex h-7 items-center gap-1.5 whitespace-nowrap rounded-lg px-2.5 text-[11px] font-semibold
                          {{ $isService ? 'bg-slate-800 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700' }}">
                    <x-icon name="scissors" class="h-3.5 w-3.5" /> Cortes ({{ (int) $summary->service_count }})
                </a>
                <a href="{{ $q(['type' => \App\Models\Sale::TYPE_PRODUCT]) }}"
                   class="flex h-7 items-center gap-1.5 whitespace-nowrap rounded-lg px-2.5 text-[11px] font-semibold
                          {{ $isProduct ? 'bg-slate-800 text-white' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700' }}">
                    <x-icon name="bag" class="h-3.5 w-3.5" /> Productos ({{ (int) $summary->product_count }})
                </a>
            </div>

            @unless (auth()->user()->isBarber())
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-0.5">
                    <a href="{{ $q(['barber_id' => null]) }}"
                       class="flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold transition
                              {{ ! request('barber_id') ? 'bg-amber-100 text-amber-700' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700' }}">
                        <x-icon name="users" class="h-4 w-4" /> Todos
                    </a>
                    @foreach ($barbers as $barber)
                        <a href="{{ $q(['barber_id' => $barber->id]) }}"
                           class="flex shrink-0 items-center gap-1.5 rounded-full px-2 py-1 text-xs transition
                                  {{ (int) request('barber_id') === $barber->id ? 'bg-amber-100 font-semibold text-amber-700' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700' }}">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-600">
                                {{ mb_strtoupper(mb_substr($barber->name, 0, 1)) }}
                            </span>
                            {{ $barber->name }}
                        </a>
                    @endforeach
                </div>
            @endunless
        </div>
        </div>

        <!-- Resultados -->
        <div id="sales-results" class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Transacciones · {{ $periodLabel }}</span>
                <span class="text-[11px] font-semibold text-amber-600">Más recientes</span>
            </div>

            @forelse ($sales as $sale)
                @php($isServiceSale = $sale->type === \App\Models\Sale::TYPE_SERVICE)
                <div class="space-y-2.5 rounded-xl bg-white p-3.5 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $isServiceSale ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600' }}">
                                <x-icon :name="$isServiceSale ? 'scissors' : 'bag'" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('sales.show', $sale) }}" class="text-sm font-bold text-slate-700 hover:text-amber-600">{{ $sale->code }}</a>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">{{ $sale->sold_at->diffForHumans() }}</span>
                                </div>
                                <p class="truncate text-xs font-medium {{ $isServiceSale ? 'text-amber-600' : 'text-emerald-600' }}">
                                    {{ $sale->items->map(fn ($item) => $item->name.($item->quantity > 1 ? ' (x'.$item->quantity.')' : ''))->join(', ') }}
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="block text-sm font-extrabold text-slate-800">{{ usd($sale->total_usd) }}</span>
                            <span class="text-[11px] text-slate-400">{{ ves($sale->total_ves) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 rounded-lg bg-slate-50 p-2.5">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            @if ($sale->barber)
                                <span class="flex items-center gap-1">
                                    <x-icon name="user" class="h-3.5 w-3.5 text-slate-400" />
                                    <span class="text-slate-700">{{ $sale->barber->name }}</span>
                                </span>
                                <span class="text-slate-300">•</span>
                            @endif
                            <span class="flex items-center gap-1">
                                <x-icon :name="$methodIcon($sale->payment_method)" class="h-3.5 w-3.5 text-slate-400" />
                                {{ $sale->payment_method ?: 'Sin método' }}
                            </span>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <x-badge :tone="$sale->isCancelled() ? 'red' : 'green'">{{ $sale->isCancelled() ? 'Anulada' : 'Completada' }}</x-badge>
                            <a href="{{ route('sales.show', $sale) }}" title="Ver recibo"
                               class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-slate-400 ring-1 ring-slate-200 transition hover:text-slate-700">
                                <x-icon name="receipt" class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl bg-white px-4 py-12 text-center text-sm text-slate-400 shadow-sm ring-1 ring-slate-200">
                    No se encontraron ventas con estos filtros.
                </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if ($sales->hasPages() || $sales->total() > 0)
            <div class="flex flex-col items-center gap-2 pb-2">
                <span class="text-xs text-slate-400">
                    Mostrando {{ $sales->firstItem() }}-{{ $sales->lastItem() }} de {{ $sales->total() }} ventas
                </span>
                @if ($sales->hasMorePages())
                    <a href="{{ $sales->nextPageUrl() }}" data-load-more
                       class="flex w-full items-center justify-center gap-2 rounded-xl bg-white py-3 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">
                        <x-icon name="chevron-down" class="h-4 w-4 text-amber-500" />
                        Cargar transacciones anteriores
                    </a>
                @endif
            </div>
        @endif
        </div>
    </div>

    <script>
        (function () {
            var key = 'sales-history:scroll:' + window.location.pathname;
            var saved = sessionStorage.getItem(key);

            if (saved !== null) {
                sessionStorage.removeItem(key);
                var y = parseInt(saved, 10) || 0;
                var restore = function () { window.scrollTo(0, y); };
                document.addEventListener('DOMContentLoaded', restore);
                window.addEventListener('load', restore);
            }

            document.addEventListener('click', function (event) {
                if (event.target.closest('[data-load-more]')) {
                    sessionStorage.setItem(key, window.scrollY);
                }
            });
        })();
    </script>
</x-app-layout>
