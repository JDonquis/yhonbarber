@php
    $maxCommission = $topBarbers->max('commission') ?: 1;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Panel de control</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4">
        <!-- Encabezado -->
        <section class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald-700">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    Jornada en vivo
                </span>
                <a href="{{ request()->fullUrl() }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50 active:scale-95">
                    <x-icon name="refresh" class="h-4 w-4 text-amber-500" /> Sincronizar
                </a>
            </div>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-800">Panel de Control</h1>
                <p class="mt-0.5 text-xs text-slate-400">Resumen de ventas, caja del día y rendimiento del equipo</p>
            </div>
        </section>

        <!-- KPIs -->
        <section class="grid grid-cols-2 gap-3">
            <div class="flex flex-col justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400">Venta de hoy</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <x-icon name="dollar" class="h-5 w-5" />
                    </span>
                </div>
                <span class="text-xl font-extrabold tracking-tight text-slate-800">{{ usd($todayTotal) }}</span>
                <span class="mt-0.5 text-[11px] text-slate-400">{{ $todayTickets }} ticket(s) hoy</span>
            </div>

            <div class="flex flex-col justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400">Comisión hoy</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <x-icon name="users" class="h-5 w-5" />
                    </span>
                </div>
                <span class="text-xl font-extrabold tracking-tight text-slate-800">{{ usd($todayCommission) }}</span>
                <span class="mt-0.5 text-[11px] text-amber-600">{{ $isBarber ? 'Tu comisión del día' : 'Total barberos' }}</span>
            </div>

            <div class="flex flex-col justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400">Servicios hoy</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <x-icon name="scissors" class="h-5 w-5" />
                    </span>
                </div>
                <span class="text-xl font-extrabold tracking-tight text-slate-800">{{ usd($serviceTotal) }}</span>
                <span class="mt-0.5 text-[11px] text-slate-400">{{ $serviceCount }} realizados</span>
            </div>

            <div class="flex flex-col justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400">Productos hoy</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <x-icon name="bag" class="h-5 w-5" />
                    </span>
                </div>
                <span class="text-xl font-extrabold tracking-tight text-slate-800">{{ usd($productTotal) }}</span>
                <span class="mt-0.5 text-[11px] text-slate-400">{{ $productCount }} unidades</span>
            </div>
        </section>

        @unless ($isBarber)
            <!-- Ganancia de la tienda (neto del día) -->
            <section class="flex items-center justify-between gap-3 rounded-xl bg-emerald-50 p-4 shadow-sm ring-1 ring-emerald-200">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500 text-white">
                        <x-icon name="dollar" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Ganancia de la tienda hoy</p>
                        <p class="text-[11px] text-emerald-700">Neto del estudio (ventas − comisiones)</p>
                    </div>
                </div>
                <span class="shrink-0 text-2xl font-extrabold tracking-tight text-emerald-700">{{ usd($todayShopAmount) }}</span>
            </section>
        @endunless

        <!-- Tasa del día -->
        <section class="relative overflow-hidden rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="mb-2 flex items-center justify-between">
                <span class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    <x-icon name="refresh" class="h-4 w-4 text-amber-500" /> Tasa oficial del día
                </span>
                <span class="rounded-full px-2 py-0.5 text-[11px] font-bold text-amber-600 ring-1 ring-amber-200">
                    {{ app(\App\Services\ExchangeRateService::class)->usingManual() ? 'Manual' : 'BCV' }}
                </span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-extrabold tracking-tight text-amber-600">{{ ves($currentRate) }}</span>
                <span class="text-xs font-semibold text-slate-400">/ 1,00 USD</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Cotización sincronizada automáticamente.</p>
            @if (! $isBarber)
                <form method="POST" action="{{ route('exchange-rate.refresh') }}" class="mt-3">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-100 py-2.5 text-sm font-semibold text-amber-600 transition hover:bg-slate-200 active:scale-[0.98]">
                        <x-icon name="refresh" class="h-4 w-4" /> Actualizar ahora
                    </button>
                </form>
            @endif
        </section>

        <!-- Últimas ventas -->
        <section class="space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-sm font-bold text-slate-800">
                    <x-icon name="receipt" class="h-5 w-5 text-amber-500" /> Últimas ventas
                </span>
                <a href="{{ route('sales.index') }}" class="flex items-center gap-0.5 text-xs font-bold text-amber-600 transition hover:text-amber-700">
                    Ver todas <span class="text-base leading-none">&rsaquo;</span>
                </a>
            </div>

            <div class="space-y-2">
                @forelse ($recentSales as $sale)
                    @php($isService = $sale->type === \App\Models\Sale::TYPE_SERVICE)
                    @php($barber = $sale->barber)
                    @php($words = preg_split('/\s+/', trim($barber->name ?? '')))
                    <div class="flex items-center justify-between gap-2 rounded-xl bg-white p-3.5 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">
                        <div class="min-w-0">
                            <div class="mb-1 flex items-center gap-2">
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-amber-600">{{ $sale->code }}</span>
                                <span class="text-[11px] font-semibold {{ $isService ? 'text-amber-600' : 'text-emerald-600' }}">
                                    {{ $isService ? 'Corte / Servicio' : 'Tienda directa' }}
                                </span>
                            </div>
                            <p class="truncate text-sm font-semibold text-slate-700">{{ $sale->items->pluck('name')->join(', ') }}</p>
                            <div class="mt-1 flex items-center gap-1.5">
                                @if ($barber)
                                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-emerald-100 text-[9px] font-bold text-emerald-600">
                                        {{ mb_strtoupper(mb_substr($words[0] ?? '', 0, 1)) }}
                                    </span>
                                    <span class="truncate text-[11px] text-slate-400">{{ $barber->name }}</span>
                                @else
                                    <x-icon name="bag" class="h-3.5 w-3.5 text-slate-400" />
                                    <span class="truncate text-[11px] text-slate-400">Venta tienda</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-col items-end">
                            <span class="text-sm font-extrabold text-slate-800">{{ usd($sale->total_usd) }}</span>
                            <span class="text-[11px] {{ $sale->isCancelled() ? 'text-rose-500' : 'text-emerald-600' }}">
                                {{ $sale->isCancelled() ? 'Anulada' : ($sale->payment_method ?: 'Pagado') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl bg-white px-4 py-10 text-center text-sm text-slate-400 shadow-sm ring-1 ring-slate-200">
                        Todavía no hay ventas registradas.
                    </div>
                @endforelse
            </div>
        </section>

        @unless ($isBarber)
            <!-- Stock bajo -->
            <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="mb-3 flex items-center justify-between">
                    <span class="flex items-center gap-2 text-sm font-bold text-slate-800">
                        <x-icon name="box" class="h-5 w-5 text-emerald-600" /> Stock bajo
                    </span>
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $lowStock->isEmpty() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $lowStock->isEmpty() ? 'Al día' : $lowStock->count().' por reponer' }}
                    </span>
                </div>

                @if ($lowStock->isEmpty())
                    <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            <x-icon name="check" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Todo el inventario en orden</p>
                            <p class="text-xs text-slate-400">Ningún producto requiere reposición urgente.</p>
                        </div>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($lowStock as $product)
                            <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                                <span class="truncate text-sm text-slate-700">{{ $product->name }}</span>
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-700">{{ $product->stock }} uds</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <!-- Ranking de barberos -->
            <section class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="flex items-center gap-2 text-sm font-bold text-slate-800">
                            <x-icon name="chart" class="h-5 w-5 text-amber-500" /> Ranking de barberos
                        </span>
                        <span class="text-[11px] text-slate-400">Calculado por comisión neta generada</span>
                    </div>
                    <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-amber-600">Mes en curso</span>
                </div>

                <div class="space-y-2">
                    @forelse ($topBarbers as $index => $barber)
                        @php($words = preg_split('/\s+/', trim($barber->name)))
                        @php($initials = mb_strtoupper(mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1)))
                        @php($width = $maxCommission > 0 ? round(($barber->commission ?? 0) / $maxCommission * 100) : 0)
                        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="relative shrink-0">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold {{ $index === 0 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $initials }}
                                        </span>
                                        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full text-[10px] font-extrabold {{ $index === 0 ? 'bg-amber-500 text-slate-900' : 'bg-slate-200 text-slate-500' }}">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="truncate text-sm font-bold text-slate-800">{{ $barber->name }}</span>
                                        <span class="block text-[11px] text-slate-400">{{ $barber->tickets }} corte(s) completados</span>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span class="block text-sm font-extrabold {{ $index === 0 ? 'text-amber-600' : 'text-slate-700' }}">{{ usd($barber->commission ?? 0) }}</span>
                                    <span class="text-[10px] uppercase tracking-wide text-slate-400">Comisión</span>
                                </div>
                            </div>
                            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full {{ $index === 0 ? 'bg-amber-500' : ($index === 1 ? 'bg-amber-400' : 'bg-emerald-500') }}" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl bg-white px-4 py-8 text-center text-sm text-slate-400 shadow-sm ring-1 ring-slate-200">
                            Sin datos de barberos todavía.
                        </div>
                    @endforelse
                </div>
            </section>
        @else
            <!-- Resumen del barbero -->
            <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tu mes</span>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div class="rounded-lg bg-slate-50 p-3">
                        <span class="block text-xs text-slate-400">Ventas</span>
                        <span class="text-lg font-bold text-slate-800">{{ usd($monthTotal) }}</span>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <span class="block text-xs text-slate-400">Comisión</span>
                        <span class="text-lg font-bold text-emerald-600">{{ usd($monthCommission) }}</span>
                    </div>
                </div>
            </section>
        @endunless

        <!-- Acción rápida -->
        <a href="{{ route('sales.create-service') }}"
           class="flex min-h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 text-sm font-bold text-slate-900 shadow-lg shadow-amber-500/30 transition-all hover:opacity-95 active:scale-[0.985]">
            <x-icon name="plus" class="h-5 w-5" /> Registrar venta / servicio
        </a>
    </div>
</x-app-layout>
