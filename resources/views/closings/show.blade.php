@php
    $services = collect($closing->details['servicios'] ?? []);
    $products = collect($closing->details['productos'] ?? []);
    $barbers = collect($closing->details['barberos'] ?? []);
    $methods = collect($closing->details['metodos_pago'] ?? []);

    $serviceQty = (int) $services->sum('quantity');
    $productQty = (int) $products->sum('quantity');
    $commissionPct = (float) $closing->total_usd > 0
        ? (int) round((float) $closing->barber_commission_usd / (float) $closing->total_usd * 100)
        : 0;

    $methodIcon = function (?string $method) {
        $m = mb_strtolower((string) $method);
        return match (true) {
            str_contains($m, 'móvil'), str_contains($m, 'movil') => 'phone',
            str_contains($m, 'punto'), str_contains($m, 'débito'), str_contains($m, 'debito'), str_contains($m, 'tarjeta') => 'card',
            str_contains($m, 'transfer'), str_contains($m, 'zelle') => 'refresh',
            default => 'dollar',
        };
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Cierre {{ ucfirst($closing->period_type) }}</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4">
        <!-- Volver + estado -->
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('closings.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-amber-600 transition hover:text-amber-700">
                <x-icon name="arrow-left" class="h-4 w-4" /> Volver a cierres
            </a>
            @if ($closing->isClosed())
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Cerrado
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-amber-700">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-amber-500"></span> Abierto
                </span>
            @endif
        </div>

        <!-- Título + acciones -->
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <span class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Auditoría operativa</span>
                <h1 class="truncate text-xl font-extrabold tracking-tight text-slate-800">Cierre {{ ucfirst($closing->period_type) }}</h1>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('closings.print', $closing) }}" target="_blank" title="Imprimir reporte"
                   class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-100 active:scale-95">
                    <x-icon name="printer" class="h-4 w-4" />
                </a>
            </div>
        </div>

        <!-- Banner estado -->
        <div class="flex items-center gap-2.5 rounded-xl px-4 py-2.5 {{ $closing->isClosed() ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
            <x-icon name="{{ $closing->isClosed() ? 'check' : 'clock' }}" class="h-5 w-5 shrink-0" />
            <span class="text-xs font-medium">
                Reporte {{ $closing->isClosed() ? 'cerrado y auditado' : 'en curso' }} — Sincronizado con las ventas del período
            </span>
        </div>

        <!-- KPIs -->
        <section class="grid grid-cols-2 gap-3">
            <div class="flex flex-col justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <span class="truncate text-xs text-slate-400">Servicios</span>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <x-icon name="scissors" class="h-4 w-4" />
                    </span>
                </div>
                <span class="truncate text-lg font-bold tracking-tight text-slate-800">{{ usd($closing->total_services_usd) }}</span>
                <span class="text-[11px] text-slate-400">{{ $serviceQty }} atenciones</span>
            </div>

            <div class="flex flex-col justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <span class="truncate text-xs text-slate-400">Productos</span>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <x-icon name="bag" class="h-4 w-4" />
                    </span>
                </div>
                <span class="truncate text-lg font-bold tracking-tight text-slate-800">{{ usd($closing->total_products_usd) }}</span>
                <span class="text-[11px] text-slate-400">{{ $productQty }} artículos retail</span>
            </div>

            <div class="flex flex-col justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <span class="truncate text-xs text-slate-400">Comisiones</span>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <x-icon name="users" class="h-4 w-4" />
                    </span>
                </div>
                <span class="truncate text-lg font-bold tracking-tight text-slate-800">{{ usd($closing->barber_commission_usd) }}</span>
                <span class="text-[11px] font-semibold text-emerald-600">A liquidar staff · {{ $commissionPct }}%</span>
            </div>

            <div class="flex flex-col justify-between rounded-xl bg-emerald-50 p-4 shadow-sm ring-1 ring-emerald-200">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <span class="truncate text-xs font-bold text-emerald-700">Monto tienda</span>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-500 text-white">
                        <x-icon name="dollar" class="h-4 w-4" />
                    </span>
                </div>
                <span class="truncate text-lg font-extrabold tracking-tight text-emerald-700">{{ usd($closing->shop_amount_usd) }}</span>
                <span class="text-[11px] text-emerald-700">Neto libre estudio</span>
            </div>
        </section>

        <!-- Resumen del período -->
        <section class="space-y-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-sm font-bold text-slate-800">
                    <x-icon name="clock" class="h-5 w-5 text-amber-500" /> Resumen del período
                </span>
                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold uppercase text-slate-500">{{ $closing->period_type }}</span>
            </div>
            <div class="space-y-1.5 text-sm">
                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                    <span class="text-slate-500">Rango de fechas</span>
                    <span class="font-semibold text-slate-700">{{ $closing->period_start->format('d/m/Y') }} — {{ $closing->period_end->format('d/m/Y') }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg px-3 py-2">
                    <span class="text-slate-500">Total operaciones</span>
                    <span class="font-semibold text-slate-700">{{ $closing->ticket_count }} tickets</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                    <span class="text-slate-500">Tasa promedio del período</span>
                    <span class="font-semibold text-amber-600">Bs. {{ number_format($closing->average_rate ?: $closing->exchange_rate, 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg px-3 py-2">
                    <span class="text-slate-500">Tasa de cierre</span>
                    <span class="font-semibold text-slate-600">Bs. {{ number_format($closing->exchange_rate, 2, ',', '.') }}</span>
                </div>
                <div class="mt-1 rounded-lg bg-slate-100 px-3 py-2.5">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="block text-xs text-slate-400">Total recaudado</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Monto consolidado</span>
                        </div>
                        <span class="text-2xl font-extrabold leading-none text-amber-600">{{ usd($closing->total_usd) }}</span>
                    </div>
                    <div class="mt-2 space-y-1 border-t border-slate-200 pt-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Bs cobrados (tasa histórica)</span>
                            <span class="font-bold text-emerald-600">{{ ves($closing->total_ves) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Equivalente a tasa de cierre</span>
                            <span class="font-semibold text-slate-500">{{ ves($closing->total_ves_reference > 0 ? $closing->total_ves_reference : to_ves($closing->total_usd, (float) $closing->exchange_rate)) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($closing->closed_at)
                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3 text-xs text-slate-400">
                    <span>Cerrado por <span class="font-semibold text-slate-600">{{ $closing->closedBy->name ?? '—' }}</span></span>
                    <span>{{ $closing->closed_at->format('d/m/Y h:i a') }}</span>
                </div>
            @endif
            @if ($closing->reopened_at)
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-amber-600">
                    <span>Reabierto por <span class="font-semibold">{{ $closing->reopenedBy->name ?? '—' }}</span></span>
                    <span>{{ $closing->reopened_at->format('d/m/Y h:i a') }}</span>
                </div>
            @endif
            @if ($closing->isClosed() && $closing->notes)
                <p class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500">{{ $closing->notes }}</p>
            @endif
        </section>

        <!-- Por barbero -->
        <section class="space-y-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-sm font-bold text-slate-800">
                    <x-icon name="users" class="h-5 w-5 text-emerald-600" /> Por barbero
                </span>
                <span class="text-xs text-slate-400">{{ $barbers->count() }} responsables</span>
            </div>
            <div class="space-y-2">
                @forelse ($barbers as $row)
                    @php($isStore = in_array($row['name'] ?? '', ['Sin barbero', 'Venta tienda'], true))
                    @php($words = preg_split('/\s+/', trim($row['name'] ?? '')))
                    @php($initials = mb_strtoupper(mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1)))
                    <div class="rounded-xl bg-slate-50 p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $isStore ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600' }}">
                                    {{ $isStore ? '—' : $initials }}
                                </span>
                                <div>
                                    <span class="text-sm font-semibold text-slate-700">{{ $isStore ? 'Venta tienda' : $row['name'] }}</span>
                                    @if ($isStore)
                                        <span class="block text-[10px] font-semibold text-amber-600">Sin comisión de barbero</span>
                                    @endif
                                </div>
                            </div>
                            <span class="rounded-full bg-white px-2 py-0.5 text-[11px] font-semibold text-slate-500 ring-1 ring-slate-200">{{ $row['tickets'] }} tickets</span>
                        </div>
                        <div class="mt-1.5 flex items-center justify-between text-xs">
                            <span class="text-slate-400">Producción: <strong class="font-bold text-slate-600">{{ usd($row['total_usd']) }}</strong></span>
                            <span class="font-semibold text-emerald-600">
                                {{ $isStore ? '100% Estudio' : 'Comisión '.usd($row['commission_usd']) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="rounded-xl bg-slate-50 px-3 py-6 text-center text-xs text-slate-400">Sin datos.</p>
                @endforelse
            </div>
        </section>

        <!-- Por método de pago -->
        <section class="space-y-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-sm font-bold text-slate-800">
                    <x-icon name="dollar" class="h-5 w-5 text-emerald-600" /> Por método de pago
                </span>
                <span class="text-xs text-slate-400">{{ $methods->count() }} vías</span>
            </div>
            <div class="space-y-1.5">
                @forelse ($methods as $row)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <div class="flex items-center gap-2.5">
                            <x-icon :name="$methodIcon($row['name'])" class="h-[18px] w-[18px] text-slate-400" />
                            <span class="text-sm font-medium text-slate-700">{{ $row['name'] }}</span>
                            <span class="rounded bg-white px-1.5 py-0.5 text-[11px] font-semibold text-slate-400 ring-1 ring-slate-200">{{ $row['count'] }}</span>
                        </div>
                        <span class="text-sm font-bold text-slate-700">{{ usd($row['total_usd']) }}</span>
                    </div>
                @empty
                    <p class="rounded-lg bg-slate-50 px-3 py-6 text-center text-xs text-slate-400">Sin datos.</p>
                @endforelse
            </div>
        </section>

        <!-- Servicios vendidos -->
        <section class="space-y-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-sm font-bold text-slate-800">
                    <x-icon name="scissors" class="h-5 w-5 text-amber-600" /> Servicios vendidos
                </span>
                <span class="text-xs text-slate-400">{{ $services->count() }} ítems</span>
            </div>
            <div class="space-y-1.5">
                @forelse ($services as $row)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5">
                        <div>
                            <p class="text-sm font-semibold text-slate-700">{{ $row['name'] }}</p>
                            <p class="text-xs text-slate-400">{{ $row['quantity'] }} atenciones realizadas</p>
                        </div>
                        <span class="text-sm font-bold text-amber-600">{{ usd($row['total_usd']) }}</span>
                    </div>
                @empty
                    <p class="rounded-lg bg-slate-50 px-3 py-6 text-center text-xs text-slate-400">Sin servicios.</p>
                @endforelse
            </div>
        </section>

        <!-- Productos vendidos -->
        <section class="space-y-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-sm font-bold text-slate-800">
                    <x-icon name="bag" class="h-5 w-5 text-amber-600" /> Productos vendidos
                </span>
                <span class="text-xs text-slate-400">{{ $products->count() }} ítems</span>
            </div>
            <div class="space-y-1.5">
                @forelse ($products as $row)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-white text-xs font-bold text-slate-400 ring-1 ring-slate-200">{{ $loop->iteration }}</span>
                            <span class="truncate text-sm text-slate-700">{{ $row['name'] }}</span>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-xs text-slate-400">{{ $row['quantity'] }} cant</span>
                            <span class="text-sm font-bold text-slate-700">{{ usd($row['total_usd']) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="rounded-lg bg-slate-50 px-3 py-6 text-center text-xs text-slate-400">Sin productos.</p>
                @endforelse
            </div>
        </section>

        <!-- Cerrar / Reabrir -->
        @unless ($closing->isClosed())
            <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center gap-2">
                    <x-icon name="lock" class="h-5 w-5 text-rose-500" />
                    <span class="text-sm font-bold text-slate-800">Cerrar período</span>
                </div>
                <p class="mt-1 text-xs text-slate-400">Al cerrar, los totales quedan congelados y no se podrán registrar más ventas en este período.</p>
                <form method="POST" action="{{ route('closings.close', $closing) }}" class="mt-3 space-y-3"
                      onsubmit="return confirm('¿Confirmas el cierre? Esta acción bloquea el período.');">
                    @csrf
                    <textarea name="notes" rows="2" placeholder="Notas del cierre (opcional)"
                              class="block w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500">{{ old('notes') }}</textarea>
                    <button type="submit"
                            class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-500 to-rose-400 text-sm font-bold text-white shadow-md transition hover:opacity-95 active:scale-[0.985]">
                        <x-icon name="lock" class="h-4 w-4" /> Cerrar y bloquear período
                    </button>
                </form>
            </section>
        @else
            <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center gap-2">
                    <x-icon name="refresh" class="h-5 w-5 text-amber-500" />
                    <span class="text-sm font-bold text-slate-800">Reabrir período</span>
                </div>
                <p class="mt-1 text-xs text-slate-400">Si el cierre se hizo por error, puedes reabrirlo. Los totales se recalcularán automáticamente.</p>
                <form method="POST" action="{{ route('closings.reopen', $closing) }}" class="mt-3"
                      onsubmit="return confirm('¿Reabrir este período? Podrás registrar y corregir ventas de nuevo.');">
                    @csrf
                    <button type="submit"
                            class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-white text-sm font-semibold text-amber-700 ring-1 ring-amber-300 transition hover:bg-amber-50 active:scale-[0.985]">
                        <x-icon name="refresh" class="h-4 w-4" /> Reabrir período
                    </button>
                </form>
            </section>
        @endunless
    </div>
</x-app-layout>
