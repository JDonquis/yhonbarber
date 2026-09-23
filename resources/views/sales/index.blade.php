<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Historial de ventas</h1>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('sales.create-service') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">
                <x-icon name="scissors" class="h-4 w-4" /> Registrar corte
            </a>
            <a href="{{ route('sales.create-product') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                <x-icon name="cart" class="h-4 w-4" /> Venta de producto
            </a>
        </div>

        <x-card title="Filtros">
            <form method="GET" class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                @unless (auth()->user()->isBarber())
                    <div>
                        <x-input-label for="barber_id" value="Barbero" />
                        <select id="barber_id" name="barber_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="">Todos</option>
                            @foreach ($barbers as $barber)
                                <option value="{{ $barber->id }}" @selected(request('barber_id') == $barber->id)>{{ $barber->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endunless
                <div>
                    <x-input-label for="type" value="Tipo" />
                    <select id="type" name="type" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Todos</option>
                        <option value="servicio" @selected(request('type') === 'servicio')>Servicio</option>
                        <option value="producto" @selected(request('type') === 'producto')>Producto</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="from" value="Desde" />
                    <x-text-input id="from" name="from" type="date" class="mt-1 block w-full" :value="request('from')" />
                </div>
                <div>
                    <x-input-label for="to" value="Hasta" />
                    <x-text-input id="to" name="to" type="date" class="mt-1 block w-full" :value="request('to')" />
                </div>
                <div class="flex gap-2">
                    <x-primary-button>Filtrar</x-primary-button>
                    <a href="{{ route('sales.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Limpiar</a>
                </div>
            </form>
        </x-card>

        <x-card title="Ventas">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Código</th>
                            <th class="px-5 py-3 font-medium">Fecha</th>
                            <th class="px-5 py-3 font-medium">Detalle</th>
                            <th class="px-5 py-3 font-medium">Barbero</th>
                            <th class="px-5 py-3 font-medium">Pago</th>
                            <th class="px-5 py-3 font-medium text-right">Total USD</th>
                            <th class="px-5 py-3 font-medium text-right">Total Bs</th>
                            <th class="px-5 py-3 font-medium text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($sales as $sale)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <a href="{{ route('sales.show', $sale) }}" class="font-medium text-amber-600 hover:text-amber-700">{{ $sale->code }}</a>
                                </td>
                                <td class="px-5 py-3 text-slate-500">{{ $sale->sold_at->format('d/m/Y h:i a') }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $sale->items->pluck('name')->join(', ') }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $sale->barber->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $sale->payment_method ?: '—' }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-slate-700">{{ usd($sale->total_usd) }}</td>
                                <td class="px-5 py-3 text-right text-slate-500">{{ ves($sale->total_ves) }}</td>
                                <td class="px-5 py-3 text-center">
                                    <x-badge :tone="$sale->isCancelled() ? 'red' : 'green'">{{ $sale->isCancelled() ? 'Anulada' : 'Completada' }}</x-badge>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-10 text-center text-slate-400">No se encontraron ventas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 p-4">{{ $sales->links() }}</div>
        </x-card>
    </div>
</x-app-layout>
