<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Panel de control</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-stat label="Ventas de hoy" :value="usd($todayTotal)" icon="dollar" tone="emerald"
                    :hint="$todayTickets.' ticket(s)'" />
            <x-stat label="Comisión de hoy" :value="usd($todayCommission)" icon="users" tone="amber"
                    :hint="$isBarber ? 'Tu comisión del día' : 'Total barberos'" />
            <x-stat label="Servicios de hoy" :value="usd($serviceTotal)" icon="scissors" tone="sky" />
            <x-stat label="Productos de hoy" :value="usd($productTotal)" icon="cart" tone="rose" />
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <x-card class="xl:col-span-2" title="Últimas ventas" description="Movimientos más recientes">
                <x-slot name="actions">
                    <a href="{{ route('sales.index') }}" class="text-sm font-medium text-amber-600 hover:text-amber-700">Ver todas</a>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <tr>
                                <th class="px-5 py-2 font-medium">Código</th>
                                <th class="px-5 py-2 font-medium">Detalle</th>
                                <th class="px-5 py-2 font-medium">Barbero</th>
                                <th class="px-5 py-2 font-medium text-right">Total</th>
                                <th class="px-5 py-2 font-medium">Hora</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentSales as $sale)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 font-medium text-slate-700">
                                        <a href="{{ route('sales.show', $sale) }}" class="hover:text-amber-600">{{ $sale->code }}</a>
                                    </td>
                                    <td class="px-5 py-3 text-slate-500">{{ $sale->items->pluck('name')->join(', ') }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ $sale->barber->name ?? '—' }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-slate-700">{{ usd($sale->total_usd) }}</td>
                                    <td class="px-5 py-3 text-slate-400">{{ $sale->sold_at->format('h:i a') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Todavía no hay ventas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <div class="space-y-6">
                <x-card title="Tasa del día" description="Cotización del dólar">
                    <div class="p-5">
                        <p class="text-3xl font-bold text-slate-900">{{ ves($currentRate) }}</p>
                        <p class="text-sm text-slate-400">por cada 1 $</p>
                        @if (auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('exchange-rate.refresh') }}" class="mt-3">
                                @csrf
                                <button class="inline-flex items-center gap-2 text-sm font-medium text-amber-600 hover:text-amber-700">
                                    <x-icon name="refresh" class="h-4 w-4" /> Actualizar ahora
                                </button>
                            </form>
                        @endif
                    </div>
                </x-card>

                @if ($isBarber)
                    <x-card title="Tu mes" description="Resumen del mes en curso">
                        <div class="p-5 space-y-3">
                            <div class="flex justify-between text-sm"><span class="text-slate-500">Ventas</span><span class="font-semibold">{{ usd($monthTotal) }}</span></div>
                            <div class="flex justify-between text-sm"><span class="text-slate-500">Comisión</span><span class="font-semibold">{{ usd($monthCommission) }}</span></div>
                        </div>
                    </x-card>
                @else
                    <x-card title="Stock bajo" description="Productos por reponer">
                        <div class="p-5 space-y-3">
                            @forelse ($lowStock as $product)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600">{{ $product->name }}</span>
                                    <x-badge tone="red">{{ $product->stock }} uds</x-badge>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">Todo el inventario está en niveles correctos.</p>
                            @endforelse
                        </div>
                    </x-card>
                @endif
            </div>
        </div>

        @unless ($isBarber)
            <x-card title="Ranking de barberos del mes" description="Por comisión generada">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <tr>
                                <th class="px-5 py-2 font-medium">Barbero</th>
                                <th class="px-5 py-2 font-medium text-right">Cortes</th>
                                <th class="px-5 py-2 font-medium text-right">Comisión</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($topBarbers as $barber)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-slate-700">{{ $barber->name }}</td>
                                    <td class="px-5 py-3 text-right text-slate-500">{{ $barber->tickets }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-slate-700">{{ usd($barber->commission ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400">Sin datos de barberos todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endunless
    </div>
</x-app-layout>
