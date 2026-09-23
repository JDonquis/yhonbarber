<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Cierre {{ ucfirst($closing->period_type) }}</h1>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('closings.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
                <x-icon name="arrow-left" class="h-4 w-4" /> Volver a cierres
            </a>
            <div class="flex items-center gap-2">
                <x-badge :tone="$closing->isClosed() ? 'red' : 'amber'">{{ $closing->isClosed() ? 'Cerrado' : 'Abierto' }}</x-badge>
                <a href="{{ route('closings.print', $closing) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <x-icon name="printer" class="h-4 w-4" /> Imprimir
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-stat label="Total servicios" :value="usd($closing->total_services_usd)" icon="scissors" tone="sky" />
            <x-stat label="Total productos" :value="usd($closing->total_products_usd)" icon="cart" tone="rose" />
            <x-stat label="Comisión barberos" :value="usd($closing->barber_commission_usd)" icon="users" tone="amber" />
            <x-stat label="Monto tienda" :value="usd($closing->shop_amount_usd)" icon="dollar" tone="emerald" />
        </div>

        <x-card title="Resumen del período">
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                <div><p class="text-slate-500">Desde</p><p class="font-semibold text-slate-700">{{ $closing->period_start->format('d/m/Y') }}</p></div>
                <div><p class="text-slate-500">Hasta</p><p class="font-semibold text-slate-700">{{ $closing->period_end->format('d/m/Y') }}</p></div>
                <div><p class="text-slate-500">Tickets</p><p class="font-semibold text-slate-700">{{ $closing->ticket_count }}</p></div>
                <div><p class="text-slate-500">Tasa usada</p><p class="font-semibold text-slate-700">Bs. {{ number_format($closing->exchange_rate, 2, ',', '.') }}</p></div>
                <div><p class="text-slate-500">Total USD</p><p class="text-lg font-bold text-slate-900">{{ usd($closing->total_usd) }}</p></div>
                <div><p class="text-slate-500">Total Bs</p><p class="text-lg font-bold text-slate-900">{{ ves($closing->total_ves) }}</p></div>
                @if ($closing->closed_at)
                    <div><p class="text-slate-500">Cerrado por</p><p class="font-semibold text-slate-700">{{ $closing->closedBy->name ?? '—' }}</p></div>
                    <div><p class="text-slate-500">Fecha de cierre</p><p class="font-semibold text-slate-700">{{ $closing->closed_at->format('d/m/Y h:i a') }}</p></div>
                @endif
                @if ($closing->reopened_at)
                    <div><p class="text-slate-500">Reabierto por</p><p class="font-semibold text-amber-600">{{ $closing->reopenedBy->name ?? '—' }}</p></div>
                    <div><p class="text-slate-500">Fecha de reapertura</p><p class="font-semibold text-amber-600">{{ $closing->reopened_at->format('d/m/Y h:i a') }}</p></div>
                @endif
            </div>
        </x-card>

        @if ($closing->isClosed() && $closing->notes)
            <x-card title="Notas">
                <p class="p-5 text-sm text-slate-600">{{ $closing->notes }}</p>
            </x-card>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-card title="Por barbero">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <tr><th class="px-5 py-3 font-medium">Barbero</th><th class="px-5 py-3 font-medium text-right">Tickets</th><th class="px-5 py-3 font-medium text-right">Ventas</th><th class="px-5 py-3 font-medium text-right">Comisión</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($closing->details['barberos'] ?? [] as $row)
                                <tr>
                                    <td class="px-5 py-3 text-slate-700">{{ $row['name'] }}</td>
                                    <td class="px-5 py-3 text-right text-slate-500">{{ $row['tickets'] }}</td>
                                    <td class="px-5 py-3 text-right text-slate-500">{{ usd($row['total_usd']) }}</td>
                                    <td class="px-5 py-3 text-right font-medium text-slate-700">{{ usd($row['commission_usd']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400">Sin datos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <x-card title="Por método de pago">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <tr><th class="px-5 py-3 font-medium">Método</th><th class="px-5 py-3 font-medium text-right">Tickets</th><th class="px-5 py-3 font-medium text-right">Total</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($closing->details['metodos_pago'] ?? [] as $row)
                                <tr>
                                    <td class="px-5 py-3 text-slate-700">{{ $row['name'] }}</td>
                                    <td class="px-5 py-3 text-right text-slate-500">{{ $row['count'] }}</td>
                                    <td class="px-5 py-3 text-right font-medium text-slate-700">{{ usd($row['total_usd']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400">Sin datos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-card title="Servicios vendidos">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <tr><th class="px-5 py-3 font-medium">Servicio</th><th class="px-5 py-3 font-medium text-right">Cantidad</th><th class="px-5 py-3 font-medium text-right">Total</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($closing->details['servicios'] ?? [] as $row)
                                <tr>
                                    <td class="px-5 py-3 text-slate-700">{{ $row['name'] }}</td>
                                    <td class="px-5 py-3 text-right text-slate-500">{{ $row['quantity'] }}</td>
                                    <td class="px-5 py-3 text-right font-medium text-slate-700">{{ usd($row['total_usd']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400">Sin datos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <x-card title="Productos vendidos">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <tr><th class="px-5 py-3 font-medium">Producto</th><th class="px-5 py-3 font-medium text-right">Cantidad</th><th class="px-5 py-3 font-medium text-right">Total</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($closing->details['productos'] ?? [] as $row)
                                <tr>
                                    <td class="px-5 py-3 text-slate-700">{{ $row['name'] }}</td>
                                    <td class="px-5 py-3 text-right text-slate-500">{{ $row['quantity'] }}</td>
                                    <td class="px-5 py-3 text-right font-medium text-slate-700">{{ usd($row['total_usd']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400">Sin datos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        @unless ($closing->isClosed())
            <x-card title="Cerrar período" description="Al cerrar, los totales quedan congelados y no se podrán registrar más ventas en este período.">
                <form method="POST" action="{{ route('closings.close', $closing) }}" class="p-5 space-y-4"
                      onsubmit="return confirm('¿Confirmas el cierre? Esta acción bloquea el período.');">
                    @csrf
                    <div>
                        <x-input-label for="notes" value="Notas del cierre (opcional)" />
                        <x-text-input id="notes" name="notes" class="mt-1 block w-full" :value="old('notes')" />
                    </div>
                    <div class="flex justify-end">
                        <button class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                            <x-icon name="lock" class="h-4 w-4" /> Cerrar y bloquear
                        </button>
                    </div>
                </form>
            </x-card>
        @else
            <x-card title="Reabrir período" description="Si el cierre se hizo por error, puedes reabrirlo para corregir o registrar ventas nuevamente. Al reabrir, los totales se recalcularán automáticamente.">
                <form method="POST" action="{{ route('closings.reopen', $closing) }}" class="p-5"
                      onsubmit="return confirm('¿Reabrir este período? Podrás registrar y corregir ventas de nuevo.');">
                    @csrf
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-slate-500">El período quedará nuevamente <span class="font-semibold text-amber-600">abierto</span>.</p>
                        <button class="inline-flex items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100">
                            <x-icon name="refresh" class="h-4 w-4" /> Reabrir período
                        </button>
                    </div>
                </form>
            </x-card>
        @endunless
    </div>
</x-app-layout>
