<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Inventario · {{ $product->name }}</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Volver a productos
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card class="lg:col-span-1" title="Registrar movimiento">
                <form method="POST" action="{{ route('products.stock.store', $product) }}" class="p-5 space-y-4" x-data="{ type: 'entrada' }">
                    @csrf
                    <div>
                        <x-input-label for="type" value="Tipo de movimiento" />
                        <select id="type" name="type" x-model="type" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="entrada">Entrada (sumar stock)</option>
                            <option value="salida">Salida (restar stock)</option>
                            <option value="ajuste">Ajuste (fijar stock)</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="quantity" value="Cantidad" />
                        <x-text-input id="quantity" name="quantity" type="number" min="0" class="mt-1 block w-full" required />
                        <p class="mt-1 text-xs text-slate-400" x-show="type === 'ajuste'">Ingresa el stock final deseado.</p>
                        <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="note" value="Nota" />
                        <x-text-input id="note" name="note" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('note')" class="mt-2" />
                    </div>

                    <x-primary-button class="w-full justify-center">Registrar movimiento</x-primary-button>
                </form>
            </x-card>

            <x-card class="lg:col-span-2" title="Historial de movimientos">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <tr>
                                <th class="px-5 py-3 font-medium">Fecha</th>
                                <th class="px-5 py-3 font-medium">Tipo</th>
                                <th class="px-5 py-3 font-medium text-right">Cantidad</th>
                                <th class="px-5 py-3 font-medium text-right">Stock final</th>
                                <th class="px-5 py-3 font-medium">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($movements as $movement)
                                <tr>
                                    <td class="px-5 py-3 text-slate-500">{{ $movement->created_at->format('d/m/Y h:i a') }}</td>
                                    <td class="px-5 py-3">
                                        <x-badge :tone="$movement->quantity >= 0 ? 'green' : 'red'">{{ ucfirst($movement->type) }}</x-badge>
                                    </td>
                                    <td class="px-5 py-3 text-right font-medium {{ $movement->quantity >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $movement->quantity >= 0 ? '+' : '' }}{{ $movement->quantity }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-semibold text-slate-700">{{ $movement->stock_after }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ $movement->user->name ?? 'Sistema' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Sin movimientos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 p-4">{{ $movements->links() }}</div>
            </x-card>
        </div>
    </div>
</x-app-layout>
