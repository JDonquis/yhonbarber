<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Venta {{ $sale->code }}</h1>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
                <x-icon name="arrow-left" class="h-4 w-4" /> Volver al historial
            </a>
            @if (auth()->user()->isAdmin() && ! $sale->isCancelled())
                <form method="POST" action="{{ route('sales.cancel', $sale) }}" onsubmit="return confirm('¿Anular esta venta? El stock de productos será restituido.');">
                    @csrf
                    <button class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-100">
                        <x-icon name="x" class="h-4 w-4" /> Anular venta
                    </button>
                </form>
            @endif
        </div>

        @if ($sale->isCancelled())
            <div class="flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <x-icon name="alert" class="h-5 w-5" /> Esta venta fue anulada.
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card class="lg:col-span-2" title="Detalle de la venta">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <tr>
                                <th class="px-5 py-3 font-medium">Descripción</th>
                                <th class="px-5 py-3 font-medium text-center">Cant.</th>
                                <th class="px-5 py-3 font-medium text-right">Precio</th>
                                <th class="px-5 py-3 font-medium text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td class="px-5 py-3 text-slate-700">
                                        {{ $item->name }}
                                        <span class="ml-1 text-xs text-slate-400">{{ $item->item_type === 'servicio' ? 'Servicio' : 'Producto' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center text-slate-500">{{ $item->quantity }}</td>
                                    <td class="px-5 py-3 text-right text-slate-500">{{ usd($item->unit_price_usd) }}</td>
                                    <td class="px-5 py-3 text-right font-medium text-slate-700">{{ usd($item->line_total_usd) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 p-5 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Total</span><span class="font-bold text-slate-900">{{ usd($sale->total_usd) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Total en bolívares</span><span class="font-semibold text-slate-700">{{ ves($sale->total_ves) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Tasa aplicada</span><span class="text-slate-500">Bs. {{ number_format($sale->exchange_rate, 2, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Comisión barbero ({{ rtrim(rtrim(number_format($sale->commission_rate, 2, ',', '.'), '0'), ',') }}%)</span><span class="font-semibold text-emerald-600">{{ usd($sale->barber_commission_usd) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Monto tienda</span><span class="font-semibold text-slate-700">{{ usd($sale->shop_amount_usd) }}</span></div>
                </div>
            </x-card>

            <x-card class="lg:col-span-1" title="Información">
                <div class="p-5 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Fecha</span><span class="font-medium text-slate-700">{{ $sale->sold_at->format('d/m/Y h:i a') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Barbero</span><span class="font-medium text-slate-700">{{ $sale->barber->name ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Registrada por</span><span class="font-medium text-slate-700">{{ $sale->user->name ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Método de pago</span><span class="font-medium text-slate-700">{{ $sale->payment_method ?: '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Moneda de cobro</span><span class="font-medium text-slate-700">{{ $sale->payment_currency }}</span></div>
                    @if ($sale->notes)
                        <div class="border-t border-slate-100 pt-3">
                            <p class="text-slate-500">Notas</p>
                            <p class="mt-1 text-slate-700">{{ $sale->notes }}</p>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
