<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Productos</h1>
    </x-slot>

    <div class="max-w-6xl mx-auto">
        <x-card title="Inventario" description="Productos disponibles para la venta">
            <x-slot name="actions">
                <a href="{{ route('products.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-amber-400">
                    <x-icon name="plus" class="h-4 w-4" /> Nuevo producto
                </a>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Producto</th>
                            <th class="px-5 py-3 font-medium">SKU</th>
                            <th class="px-5 py-3 font-medium text-right">Costo</th>
                            <th class="px-5 py-3 font-medium text-right">Precio</th>
                            <th class="px-5 py-3 font-medium text-center">Stock</th>
                            <th class="px-5 py-3 font-medium text-center">Estado</th>
                            <th class="px-5 py-3 font-medium text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($products as $product)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-700">{{ $product->name }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $product->sku ?: '—' }}</td>
                                <td class="px-5 py-3 text-right text-slate-500">{{ usd($product->cost) }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-slate-700">{{ usd($product->price) }}</td>
                                <td class="px-5 py-3 text-center">
                                    @if ($product->isLowStock())
                                        <x-badge tone="red">{{ $product->stock }} uds</x-badge>
                                    @else
                                        <x-badge tone="green">{{ $product->stock }} uds</x-badge>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <x-badge :tone="$product->active ? 'green' : 'red'">{{ $product->active ? 'Activo' : 'Inactivo' }}</x-badge>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('products.stock.index', $product) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100" title="Inventario">
                                            <x-icon name="box" class="h-4 w-4" />
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100" title="Editar">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('¿Eliminar este producto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50" title="Eliminar">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">No hay productos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <div class="mt-5">{{ $products->links() }}</div>
    </div>
</x-app-layout>
