<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Venta de producto</h1>
    </x-slot>

    @php
        $methodIcon = function (string $method) {
            $m = mb_strtolower($method);
            return match (true) {
                str_contains($m, 'móvil'), str_contains($m, 'movil') => 'phone',
                str_contains($m, 'punto'), str_contains($m, 'débito'), str_contains($m, 'debito'), str_contains($m, 'tarjeta') => 'card',
                str_contains($m, 'transfer'), str_contains($m, 'zelle') => 'refresh',
                default => 'dollar',
            };
        };
    @endphp

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        rate: {{ (float) $currentRate }},
        search: '',
        cart: [],
        paymentCurrency: '{{ old('payment_currency', 'USD') }}',
        paymentMethod: '{{ old('payment_method', '') }}',
        productsById: @js($products->mapWithKeys(fn ($p) => [$p->id => ['price' => (float) $p->price, 'name' => $p->name, 'stock' => (int) $p->stock]])),
        productList: @js($products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'price' => (float) $p->price, 'stock' => (int) $p->stock, 'min_stock' => (int) $p->min_stock])->values()),
        get filteredProducts() {
            const q = (this.search || '').toLowerCase().trim();
            if (! q) return this.productList;
            return this.productList.filter(p => p.name.toLowerCase().includes(q) || (p.sku || '').toLowerCase().includes(q));
        },
        inCart(id) { return this.cart.find(i => i.product_id === id); },
        addToCart(id) {
            const product = this.productsById[id];
            if (! product) return;
            const existing = this.inCart(id);
            if (existing) {
                if (existing.quantity < product.stock) existing.quantity++;
            } else if (product.stock > 0) {
                this.cart.push({ product_id: id, quantity: 1 });
            }
        },
        decrement(id) {
            const index = this.cart.findIndex(i => i.product_id === id);
            if (index === -1) return;
            if (this.cart[index].quantity > 1) this.cart[index].quantity--;
            else this.cart.splice(index, 1);
        },
        lineTotal(item) { const p = this.productsById[item.product_id]; return p ? p.price * item.quantity : 0; },
        formatVes(value) {
            return 'Bs. ' + Number(value || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        get units() { return this.cart.reduce((sum, i) => sum + i.quantity, 0); },
        get total() { return this.cart.reduce((sum, i) => sum + this.lineTotal(i), 0); },
        get totalVes() { return this.total * this.rate; }
    }">
        <form method="POST" action="{{ route('sales.store-product') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="payment_method" :value="paymentMethod">
            <input type="hidden" name="payment_currency" :value="paymentCurrency">
            <template x-for="(item, index) in cart" :key="item.product_id">
                <span>
                    <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">
                    <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                </span>
            </template>

            <!-- Barra superior -->
            <div class="flex items-center justify-between">
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-amber-600">Módulo tienda</span>
                    <p class="text-lg font-bold text-slate-800">Venta de Producto</p>
                </div>
                <div class="flex items-center gap-2 rounded-full bg-white px-3 py-1.5 shadow-sm ring-1 ring-slate-200">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                    <div class="text-right leading-tight">
                        <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Tasa hoy</span>
                        <span class="text-xs font-bold text-slate-700">{{ ves($currentRate) }}</span>
                    </div>
                </div>
            </div>

            <!-- 1. Productos en stock -->
            <x-card>
                <div class="p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                                <x-icon name="bag" class="h-4 w-4" />
                            </span>
                            <span class="text-sm font-semibold text-slate-800">Productos en stock</span>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-amber-600">
                            <span x-text="units"></span> en cesta
                        </span>
                    </div>

                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-icon name="search" class="h-5 w-5" />
                        </span>
                        <input type="text" x-model="search" placeholder="Buscar pomada, aceite, loción..."
                               class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                    </div>

                    <p class="text-xs text-slate-400">
                        Mostrando <span class="font-semibold text-slate-600" x-text="filteredProducts.length"></span>
                        de <span x-text="productList.length"></span> productos
                    </p>

                    <div class="flex max-h-[26rem] flex-col gap-2 overflow-y-auto pr-1">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <article class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3 transition"
                                     :class="inCart(product.id) ? 'ring-2 ring-amber-500 bg-amber-50' : 'hover:bg-slate-100'">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg"
                                          :class="inCart(product.id) ? 'bg-amber-500 text-slate-900' : 'bg-slate-200 text-slate-500'">
                                        <x-icon name="box" class="h-5 w-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-700" x-text="product.name"></p>
                                        <p class="text-xs font-medium"
                                           :class="product.stock <= product.min_stock ? 'text-rose-500' : 'text-emerald-600'">
                                            <span x-text="product.stock <= product.min_stock ? 'Solo ' + product.stock + ' en tienda' : product.stock + ' disponibles'"></span>
                                            <span class="text-slate-400" x-show="product.sku" x-cloak> · <span x-text="product.sku"></span></span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-slate-800" x-text="'$' + product.price.toFixed(2)"></p>
                                        <p class="text-[11px] text-slate-400" x-text="formatVes(product.price * rate)"></p>
                                    </div>

                                    <button type="button" x-show="! inCart(product.id)" @click="addToCart(product.id)"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-200 text-amber-600 transition hover:bg-slate-300 active:scale-90"
                                            :disabled="product.stock < 1">
                                        <x-icon name="plus" class="h-4 w-4" />
                                    </button>

                                    <div x-show="inCart(product.id)" x-cloak class="flex items-center gap-1.5 rounded-lg bg-white p-1 shadow-sm ring-1 ring-amber-200">
                                        <button type="button" @click="decrement(product.id)"
                                                class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-100 text-slate-600 transition hover:bg-slate-200 active:scale-90">
                                            <x-icon name="minus" class="h-3.5 w-3.5" />
                                        </button>
                                        <span class="min-w-[20px] text-center text-sm font-bold text-slate-800" x-text="inCart(product.id)?.quantity"></span>
                                        <button type="button" @click="addToCart(product.id)"
                                                class="flex h-7 w-7 items-center justify-center rounded-md bg-amber-500 text-slate-900 transition hover:bg-amber-400 active:scale-90">
                                            <x-icon name="plus" class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </template>

                        <div x-show="filteredProducts.length === 0" x-cloak class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-400">
                            No hay productos que coincidan con la búsqueda.
                        </div>
                    </div>

                    <x-input-error :messages="$errors->get('items')" />
                </div>
            </x-card>

            <!-- 2. Método de cobro -->
            <x-card>
                <div class="p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                <x-icon name="dollar" class="h-4 w-4" />
                            </span>
                            <span class="text-sm font-semibold text-slate-800">Método de cobro</span>
                        </div>
                        <span class="text-xs font-semibold text-amber-600" x-text="paymentMethod || 'Selecciona uno'"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($paymentMethods as $method)
                            <button type="button" @click="paymentMethod = @js($method)"
                                    :class="paymentMethod === @js($method) ? 'ring-2 ring-amber-500 bg-amber-50' : 'bg-slate-50 hover:bg-slate-100'"
                                    class="flex items-center gap-2 rounded-lg p-2.5 text-left transition">
                                <x-icon :name="$methodIcon($method)" class="h-5 w-5 shrink-0 text-amber-500" />
                                <span class="truncate text-sm font-semibold text-slate-700">{{ $method }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <span class="text-xs font-medium text-slate-500">Moneda de cobro</span>
                        <div class="inline-flex rounded-lg bg-slate-100 p-0.5">
                            <button type="button" @click="paymentCurrency = 'USD'"
                                    :class="paymentCurrency === 'USD' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'"
                                    class="rounded-md px-3 py-1 text-xs font-bold uppercase tracking-wide transition">USD</button>
                            <button type="button" @click="paymentCurrency = 'VES'"
                                    :class="paymentCurrency === 'VES' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'"
                                    class="rounded-md px-3 py-1 text-xs font-bold uppercase tracking-wide transition">VES</button>
                        </div>
                    </div>

                    <details class="group pt-1">
                        <summary class="flex cursor-pointer list-none items-center justify-between py-1 text-sm text-slate-500 hover:text-slate-700">
                            <span class="flex items-center gap-1.5">
                                <x-icon name="edit" class="h-4 w-4" /> Añadir notas o cliente (opcional)
                            </span>
                            <x-icon name="chevron-down" class="h-4 w-4 transition-transform group-open:rotate-180" />
                        </summary>
                        <div class="pt-2">
                            <x-text-input id="notes" name="notes" class="block w-full" :value="old('notes')" placeholder="Ej: Cliente Sr. Rodríguez — fragancia sándalo" />
                        </div>
                    </details>
                </div>
            </x-card>

            <!-- 3. Resumen -->
            <x-card>
                <div class="p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600">Desglose de facturación</span>
                        <span class="text-xs text-slate-400">Tasa: {{ ves($currentRate) }} / $1</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <div class="flex items-baseline justify-between">
                            <span class="text-sm text-slate-500">Total a pagar:</span>
                            <span class="text-2xl font-extrabold tracking-tight text-amber-600" x-text="'$ ' + total.toFixed(2) + ' USD'">$ 0.00 USD</span>
                        </div>
                        <div class="flex items-baseline justify-between">
                            <span class="text-xs text-slate-400">Equivalente:</span>
                            <span class="text-sm font-bold text-slate-700" x-text="formatVes(totalVes)">Bs. 0,00</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between rounded-lg bg-slate-100 px-3 py-2.5">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Ingreso total barbershop</span>
                        <span class="text-sm font-bold text-emerald-600" x-text="'$' + total.toFixed(2) + ' USD'">$0.00 USD</span>
                    </div>
                </div>
            </x-card>

            <!-- Acciones -->
            <div class="space-y-2 pt-1">
                <button type="submit" :disabled="cart.length === 0"
                        class="flex w-full min-h-[52px] items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 px-6 text-sm font-extrabold uppercase tracking-wide text-slate-900 shadow-lg shadow-amber-500/30 transition-all hover:opacity-95 active:scale-[0.985] disabled:cursor-not-allowed disabled:opacity-50">
                    <x-icon name="check" class="h-5 w-5" />
                    <span>Registrar venta ($<span x-text="total.toFixed(2)"></span> · <span x-text="formatVes(totalVes)"></span>)</span>
                </button>
                <p class="text-center text-xs text-slate-400" x-show="cart.length === 0" x-cloak>
                    Agrega al menos un producto para registrar la venta.
                </p>
                <a href="{{ route('sales.index') }}" class="flex items-center justify-center gap-1 py-1 text-sm text-slate-400 transition hover:text-slate-600">
                    <x-icon name="x" class="h-4 w-4" /> Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
