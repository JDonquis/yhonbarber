<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Venta de producto</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto" x-data="{
        rate: {{ (float) $currentRate }},
        products: @js($products->mapWithKeys(fn ($p) => [$p->id => ['price' => (float) $p->price, 'name' => $p->name, 'stock' => $p->stock]])),
        items: [{ product_id: '', quantity: 1 }],
        add() { this.items.push({ product_id: '', quantity: 1 }); },
        remove(i) { this.items.splice(i, 1); },
        lineTotal(item) { const p = this.products[item.product_id]; return p ? p.price * (item.quantity || 0) : 0; },
        get total() { return this.items.reduce((sum, i) => sum + this.lineTotal(i), 0); },
        get totalVes() { return (this.total * this.rate).toFixed(2); }
    }">
        <form method="POST" action="{{ route('sales.store-product') }}">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <x-card title="Productos">
                        <div class="p-6 space-y-4">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="flex flex-col sm:flex-row sm:items-end gap-3 rounded-lg border border-slate-100 p-3">
                                    <div class="flex-1">
                                        <x-input-label value="Producto" />
                                        <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" required
                                                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                            <option value="">Selecciona un producto</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }} — {{ usd($product->price) }} (stock: {{ $product->stock }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-full sm:w-28">
                                        <x-input-label value="Cantidad" />
                                        <input type="number" min="1" x-model.number="item.quantity"
                                               :name="'items[' + index + '][quantity]'"
                                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                    </div>
                                    <div class="w-full sm:w-32 pb-2 text-right font-semibold text-slate-700"
                                         x-text="'$ ' + lineTotal(item).toFixed(2)"></div>
                                    <button type="button" @click="remove(index)" x-show="items.length > 1"
                                            class="mb-1 inline-flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50" title="Quitar">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </template>

                            <button type="button" @click="add()"
                                    class="inline-flex items-center gap-2 rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                                <x-icon name="plus" class="h-4 w-4" /> Agregar producto
                            </button>
                        </div>
                    </x-card>

                    <x-card title="Datos de la venta">
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <x-input-label for="payment_method" value="Método de pago" />
                                <select id="payment_method" name="payment_method"
                                        class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                    <option value="">Selecciona</option>
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method }}">{{ $method }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="payment_currency" value="Moneda de cobro" />
                                <select id="payment_currency" name="payment_currency"
                                        class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                    <option value="USD">Dólares (USD)</option>
                                    <option value="VES">Bolívares (VES)</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <x-input-label for="notes" value="Notas (opcional)" />
                                <x-text-input id="notes" name="notes" class="mt-1 block w-full" :value="old('notes')" />
                            </div>
                        </div>
                    </x-card>
                </div>

                <x-card class="lg:col-span-1" title="Resumen">
                    <div class="p-6 space-y-4">
                        <div class="flex items-baseline justify-between">
                            <span class="text-sm text-slate-500">Total</span>
                            <span class="text-2xl font-bold text-slate-900" x-text="'$ ' + total.toFixed(2)">$ 0,00</span>
                        </div>
                        <div class="flex items-baseline justify-between border-t border-slate-100 pt-3">
                            <span class="text-sm text-slate-500">En bolívares</span>
                            <span class="font-semibold text-slate-700">Bs. <span x-text="totalVes">0,00</span></span>
                        </div>
                        <p class="text-xs text-slate-400">Tasa aplicada: Bs. {{ number_format($currentRate, 2, ',', '.') }} por $1.</p>

                        <div class="border-t border-slate-100 pt-4 space-y-3">
                            <x-primary-button class="w-full justify-center">Registrar venta</x-primary-button>
                            <a href="{{ route('sales.index') }}" class="block text-center text-sm font-medium text-slate-500 hover:text-slate-700">Cancelar</a>
                        </div>
                    </div>
                </x-card>
            </div>
        </form>
    </div>
</x-app-layout>
