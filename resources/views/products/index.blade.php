@php
    $productsData = $products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'price' => (float) $product->price,
        'cost' => (float) $product->cost,
        'stock' => (int) $product->stock,
        'min_stock' => (int) $product->min_stock,
        'active' => (bool) $product->active,
        'deletable' => $product->sale_items_count === 0,
    ])->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Productos</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        products: @js($productsData),
        search: '',
        filter: 'all',
        movementProduct: null,
        movementType: 'in',
        movementQty: 5,
        movementNote: '',
        movementError: null,
        token: '{{ csrf_token() }}',
        base: '{{ url('/products') }}',
        rate: {{ (float) $currentRate }},
        get filteredProducts() {
            const q = this.search.toLowerCase().trim();
            return this.products.filter((product) => {
                const matchesSearch = ! q
                    || product.name.toLowerCase().includes(q)
                    || (product.sku || '').toLowerCase().includes(q);
                let matchesFilter = true;
                if (this.filter === 'instock') matchesFilter = product.active && ! this.isLow(product);
                else if (this.filter === 'lowstock') matchesFilter = product.active && this.isLow(product);
                else if (this.filter === 'inactive') matchesFilter = ! product.active;
                return matchesSearch && matchesFilter;
            });
        },
        get instockCount() { return this.products.filter((p) => p.active && ! this.isLow(p)).length; },
        get lowstockCount() { return this.products.filter((p) => p.active && this.isLow(p)).length; },
        get inactiveCount() { return this.products.filter((p) => ! p.active).length; },
        isLow(product) { return product.stock <= product.min_stock; },
        margin(product) { return product.price - product.cost; },
        marginPct(product) {
            if (product.cost > 0) return (product.price - product.cost) / product.cost * 100;
            return product.price > 0 ? 100 : 0;
        },
        formatVes(value) {
            return 'Bs. ' + Number(value || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        openMovement(product, type = 'in', qty = 5) {
            this.movementProduct = product;
            this.movementType = type;
            this.movementQty = qty;
            this.movementNote = '';
            this.movementError = null;
        },
        adjustQty(delta) { this.movementQty = Math.max(1, Number(this.movementQty || 1) + delta); },
        async submitMovement() {
            if (! this.movementProduct) return;
            this.movementError = null;
            const body = new FormData();
            body.append('_token', this.token);
            body.append('type', this.movementType === 'out' ? 'salida' : 'entrada');
            body.append('quantity', this.movementQty);
            if (this.movementNote) body.append('note', this.movementNote);
            const response = await fetch(this.base + '/' + this.movementProduct.id + '/stock', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: body,
            });
            if (response.ok) {
                const data = await response.json();
                this.movementProduct.stock = data.stock;
                this.movementProduct = null;
            } else {
                const data = await response.json().catch(() => ({}));
                this.movementError = data.errors?.quantity?.[0] || data.message || 'No se pudo registrar el movimiento.';
            }
        },
        async toggleActive(product) {
            const body = new FormData();
            body.append('_token', this.token);
            const response = await fetch(this.base + '/' + product.id + '/toggle', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: body,
            });
            if (response.ok) {
                const data = await response.json();
                product.active = data.active;
            }
        }
    }">
        <!-- Encabezado -->
        <section class="space-y-3">
            <div>
                <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-widest text-amber-600">
                    <x-icon name="box" class="h-4 w-4" /> Inventario &amp; Productos
                </span>
                <h2 class="mt-1 text-xl font-extrabold tracking-tight text-slate-800">Catálogo de Productos</h2>
                <p class="mt-0.5 text-xs text-slate-400">Control de existencias, costos, precios y movimientos de almacén.</p>
            </div>

            <!-- Métricas -->
            <div class="grid grid-cols-3 gap-2">
                <div class="rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total uds</span>
                    <p class="mt-0.5 text-lg font-bold text-slate-800">{{ $stats['units'] }}</p>
                    <span class="text-[11px] font-semibold text-emerald-600">{{ $stats['active'] }} activos</span>
                </div>
                <div class="rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Top ventas</span>
                    <p class="mt-0.5 text-lg font-bold text-amber-600">{{ $stats['topUnits'] }} uds</p>
                    <span class="truncate text-[11px] text-slate-400">{{ $stats['topName'] ?? 'Sin datos' }}</span>
                </div>
                <div class="rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Atención</span>
                    <p class="mt-0.5 text-lg font-bold text-amber-600">{{ $stats['lowStock'] }}</p>
                    <span class="text-[11px] text-amber-600">Stock crítico</span>
                </div>
            </div>

            <a href="{{ route('products.create') }}"
               class="flex h-[50px] w-full items-center justify-center gap-2 rounded-xl bg-amber-500 text-sm font-bold text-slate-900 shadow-md shadow-amber-500/25 transition hover:bg-amber-400 active:scale-[0.985]">
                <x-icon name="plus" class="h-5 w-5" /> Nuevo producto
            </a>
        </section>

        <!-- Búsqueda y filtros (fijos al hacer scroll) -->
        <section class="sticky top-16 z-20 space-y-2 bg-slate-100 pb-2 pt-2">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="search" class="h-5 w-5" />
                </span>
                <input type="text" x-model="search" placeholder="Buscar por nombre o SKU..."
                       class="h-[50px] w-full rounded-xl border border-slate-200 bg-white pl-11 pr-10 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-amber-500 focus:ring-amber-500" />
                <button type="button" x-show="search.length" x-cloak @click="search = ''"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-700" title="Limpiar">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                <button type="button" @click="filter = 'all'"
                        class="inline-flex h-9 items-center justify-center gap-1.5 whitespace-nowrap rounded-full px-3 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'all' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                    <x-icon name="box" class="h-4 w-4" /> Todos (<span x-text="products.length"></span>)
                </button>
                <button type="button" @click="filter = 'instock'"
                        class="inline-flex h-9 items-center justify-center gap-1.5 whitespace-nowrap rounded-full px-3 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'instock' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> En stock (<span x-text="instockCount"></span>)
                </button>
                <button type="button" @click="filter = 'lowstock'"
                        class="inline-flex h-9 items-center justify-center gap-1.5 whitespace-nowrap rounded-full px-3 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'lowstock' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-amber-600 ring-1 ring-slate-200 hover:text-amber-700'">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span> Stock bajo (<span x-text="lowstockCount"></span>)
                </button>
                <button type="button" @click="filter = 'inactive'"
                        class="inline-flex h-9 items-center justify-center gap-1.5 whitespace-nowrap rounded-full px-3 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'inactive' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                    Inactivos (<span x-text="inactiveCount"></span>)
                </button>
            </div>
        </section>

        <!-- Productos -->
        <section class="flex flex-col gap-3">
            <template x-for="product in filteredProducts" :key="product.id">
                <article class="flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition"
                         :class="product.active ? '' : 'opacity-80'">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                                  :class="product.active ? (isLow(product) ? 'bg-amber-100 text-amber-600' : 'bg-amber-50 text-amber-500') : 'bg-slate-100 text-slate-400'">
                                <x-icon name="box" class="h-6 w-6" />
                            </span>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-bold text-slate-800" x-text="product.name"></h3>
                                <div class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-400">
                                    <span class="font-mono tracking-wider" x-text="'SKU: ' + (product.sku || '—')"></span>
                                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                    <span x-text="'mín. ' + product.min_stock"></span>
                                </div>
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-bold"
                                  :class="isLow(product) ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'">
                                <span class="h-1.5 w-1.5 rounded-full" :class="isLow(product) ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500'"></span>
                                <span x-text="product.stock + ' en stock'"></span>
                            </span>
                            <span class="text-[11px] font-semibold" :class="product.active ? 'text-emerald-600' : 'text-slate-400'"
                                  x-text="product.active ? 'Activo' : 'Inactivo'"></span>
                        </div>
                    </div>

                    <!-- Finanzas -->
                    <div class="grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-3">
                        <div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Costo</span>
                            <p class="text-sm font-semibold text-slate-700" x-text="'$' + product.cost.toFixed(2)"></p>
                            <span class="text-[10px] text-slate-400" x-text="formatVes(product.cost * rate)"></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Precio venta</span>
                            <p class="text-sm font-bold text-amber-600" x-text="'$' + product.price.toFixed(2)"></p>
                            <span class="text-[10px] text-slate-400" x-text="formatVes(product.price * rate)"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Margen</span>
                            <p class="text-sm font-bold text-emerald-600" x-text="'+$' + margin(product).toFixed(2)"></p>
                            <span class="text-[10px] font-semibold text-emerald-600" x-text="'+' + marginPct(product).toFixed(1) + '%'"></span>
                        </div>
                    </div>

                    <!-- Alerta reposición -->
                    <template x-if="product.active && isLow(product)">
                        <div class="flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2">
                            <span class="flex items-center gap-1.5 text-[11px] font-medium text-amber-700">
                                <x-icon name="alert" class="h-4 w-4" /> Reposición recomendada
                            </span>
                            <button type="button" @click="openMovement(product, 'in', Math.max(5, product.min_stock * 2))"
                                    class="text-[11px] font-bold uppercase tracking-wider text-amber-700 hover:text-amber-800">
                                Añadir
                            </button>
                        </div>
                    </template>

                    <!-- Acciones -->
                    <div class="flex items-center gap-2 pt-1">
                        <button type="button" @click="openMovement(product, 'in')"
                                class="flex h-11 flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-100 text-xs font-semibold text-slate-700 transition hover:bg-slate-200 active:scale-[0.98]">
                            <x-icon name="refresh" class="h-4 w-4 text-amber-500" /> Movimiento
                        </button>
                        <a :href="base + '/' + product.id + '/edit'"
                           class="flex h-11 flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-100 text-xs font-semibold text-slate-700 transition hover:bg-slate-200 active:scale-[0.98]">
                            <x-icon name="edit" class="h-4 w-4 text-amber-600" /> Editar
                        </a>
                        <button type="button" @click="toggleActive(product)" :title="product.active ? 'Desactivar' : 'Activar'"
                                class="flex h-11 w-11 items-center justify-center rounded-xl transition active:scale-[0.98]"
                                :class="product.active ? 'bg-slate-100 text-slate-500 hover:text-rose-500' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'">
                            <x-icon name="power" class="h-4 w-4" />
                        </button>
                        <template x-if="product.deletable">
                            <form method="POST" :action="base + '/' + product.id" onsubmit="return confirm('¿Eliminar este producto?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Eliminar"
                                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50 text-rose-500 transition hover:bg-rose-100 active:scale-[0.98]">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </form>
                        </template>
                        <template x-if="! product.deletable">
                            <button type="button" disabled title="No se puede eliminar: tiene ventas registradas"
                                    class="flex h-11 w-11 cursor-not-allowed items-center justify-center rounded-xl bg-slate-100 text-slate-300">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </template>
                    </div>
                </article>
            </template>

            <!-- Sin resultados -->
            <div x-show="filteredProducts.length === 0" x-cloak
                 class="flex flex-col items-center justify-center rounded-xl bg-white px-6 py-12 text-center shadow-sm ring-1 ring-slate-200">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-icon name="box" class="h-7 w-7" />
                </span>
                <p class="mt-3 text-base font-bold text-slate-800">Sin resultados</p>
                <p class="mt-1 max-w-xs text-xs text-slate-400">No hay productos que coincidan con la búsqueda o el filtro.</p>
                <button type="button" @click="search = ''; filter = 'all'"
                        class="mt-4 rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-amber-600 transition hover:bg-slate-200">
                    Restablecer filtros
                </button>
            </div>
        </section>

        <!-- Modal movimiento -->
        <div x-show="movementProduct" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="movementProduct = null"></div>
            <div class="relative flex w-full max-w-md flex-col gap-4 rounded-t-2xl bg-white p-5 shadow-2xl sm:rounded-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                            <x-icon name="refresh" class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Registrar movimiento</h3>
                            <span class="text-xs text-slate-400">
                                <span x-text="movementProduct ? movementProduct.name : ''"></span>
                                · <span x-text="movementProduct ? 'SKU: ' + (movementProduct.sku || '—') : ''"></span>
                                · <span x-text="movementProduct ? 'Actual: ' + movementProduct.stock : ''"></span>
                            </span>
                        </div>
                    </div>
                    <button type="button" @click="movementProduct = null" class="text-slate-400 hover:text-slate-700">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-500">Tipo de operación</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="movementType = 'in'"
                                class="flex h-11 items-center justify-center gap-1.5 rounded-xl text-xs font-semibold transition"
                                :class="movementType === 'in' ? 'bg-emerald-500 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                            <x-icon name="plus" class="h-4 w-4" /> Entrada (+stock)
                        </button>
                        <button type="button" @click="movementType = 'out'"
                                class="flex h-11 items-center justify-center gap-1.5 rounded-xl text-xs font-semibold transition"
                                :class="movementType === 'out' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                            <x-icon name="minus" class="h-4 w-4" /> Salida (-stock)
                        </button>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-500">Cantidad</label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="adjustQty(-1)"
                                class="flex h-[50px] w-12 items-center justify-center rounded-xl bg-slate-100 text-lg font-bold text-slate-700 transition hover:bg-slate-200 active:scale-95">-</button>
                        <input type="number" min="1" x-model.number="movementQty"
                               class="h-[50px] flex-1 rounded-xl border border-slate-200 bg-slate-50 text-center text-lg font-bold text-amber-600 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                        <button type="button" @click="adjustQty(1)"
                                class="flex h-[50px] w-12 items-center justify-center rounded-xl bg-slate-100 text-lg font-bold text-slate-700 transition hover:bg-slate-200 active:scale-95">+</button>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-500">Motivo o referencia</label>
                    <input type="text" x-model="movementNote" placeholder="Ej: Compra a distribuidor / Uso en estación"
                           class="block h-[50px] w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                </div>

                <p x-show="movementError" x-cloak class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-600" x-text="movementError"></p>

                <div class="flex items-center gap-2">
                    <button type="button" @click="movementProduct = null"
                            class="h-[50px] flex-1 rounded-xl bg-slate-100 text-sm font-semibold text-slate-500 transition hover:bg-slate-200">Cancelar</button>
                    <button type="button" @click="submitMovement()"
                            class="h-[50px] flex-1 rounded-xl bg-amber-500 text-sm font-bold text-slate-900 shadow-md transition hover:bg-amber-400">Confirmar</button>
                </div>

                <a :href="movementProduct ? base + '/' + movementProduct.id + '/stock' : '#'"
                   class="text-center text-xs font-semibold text-slate-400 transition hover:text-amber-600">Ver historial completo</a>
            </div>
        </div>
    </div>
</x-app-layout>
