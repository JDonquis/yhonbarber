@php
    $servicesData = $services->map(fn ($service) => [
        'id' => $service->id,
        'name' => $service->name,
        'description' => $service->description,
        'price' => (float) $service->price,
        'active' => (bool) $service->active,
    ])->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Tipos de corte</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        services: @js($servicesData),
        search: '',
        filter: 'all',
        rate: {{ (float) $currentRate }},
        commissionRate: {{ $commissionRate }},
        editing: null,
        priceInput: 0,
        token: '{{ csrf_token() }}',
        editBase: '{{ url('/services') }}',
        get filteredServices() {
            const q = this.search.toLowerCase().trim();
            return this.services.filter((service) => {
                const matchesSearch = ! q || service.name.toLowerCase().includes(q) || (service.description || '').toLowerCase().includes(q);
                const matchesFilter = this.filter === 'all' || (this.filter === 'active' ? service.active : ! service.active);
                return matchesSearch && matchesFilter;
            });
        },
        get activeCount() { return this.services.filter((service) => service.active).length; },
        get pausedCount() { return this.services.filter((service) => ! service.active).length; },
        barberCut(service) { return service.price * this.commissionRate / 100; },
        studioCut(service) { return service.price - this.barberCut(service); },
        formatVes(value) {
            return 'Bs. ' + Number(value || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        openPrice(service) { this.editing = service; this.priceInput = service.price; },
        async savePrice() {
            if (! this.editing) return;
            const body = new FormData();
            body.append('_token', this.token);
            body.append('price', this.priceInput);
            const response = await fetch(this.editBase + '/' + this.editing.id + '/price', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: body,
            });
            if (response.ok) {
                const data = await response.json();
                this.editing.price = data.price;
                this.editing = null;
            }
        },
        async toggleActive(service) {
            const body = new FormData();
            body.append('_token', this.token);
            const response = await fetch(this.editBase + '/' + service.id + '/toggle', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: body,
            });
            if (response.ok) {
                const data = await response.json();
                service.active = data.active;
            }
        }
    }">
        <!-- Banner: tasa + ajustar -->
        <div class="flex items-center justify-between">
            <div class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 shadow-sm ring-1 ring-slate-200">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Tasa BCV: 1 USD = {{ ves($currentRate) }}</span>
            </div>
            <a href="{{ route('settings.edit') }}" class="flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-xs text-slate-500 shadow-sm ring-1 ring-slate-200 transition hover:text-amber-600">
                <x-icon name="cog" class="h-3.5 w-3.5" /> Ajustar tasa
            </a>
        </div>

        <!-- Título -->
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h2 class="text-xl font-extrabold tracking-tight text-slate-800">Cortes</h2>
                <p class="mt-0.5 text-xs text-slate-400">Control táctil de precios, comisión y catálogo en vivo</p>
            </div>
            <a href="{{ route('services.create') }}"
               class="flex h-12 shrink-0 items-center justify-center gap-1.5 rounded-xl bg-amber-500 px-4 text-sm font-bold text-slate-900 shadow-md shadow-amber-500/25 transition hover:bg-amber-400 active:scale-95">
                <x-icon name="plus" class="h-4 w-4" /> Nuevo
            </a>
        </div>

        <!-- Métricas -->
        <div class="grid grid-cols-3 gap-2">
            <div class="col-span-1 rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200">
                <span class="text-[11px] text-slate-400">Servicios</span>
                <div class="mt-0.5 flex items-baseline gap-1">
                    <span class="text-lg font-bold text-slate-800" x-text="services.length"></span>
                    <span class="text-[11px] font-semibold text-emerald-600"><span x-text="activeCount"></span> activos</span>
                </div>
                <span class="text-[10px] text-slate-400">Catálogo</span>
            </div>
            <div class="col-span-2 rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">Corte más vendido</span>
                    @if ($stats['topPct'] > 0)
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">{{ $stats['topPct'] }}% de las ventas</span>
                    @endif
                </div>
                <div class="mt-1 flex items-center gap-2">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <x-icon name="scissors" class="h-3.5 w-3.5" />
                    </span>
                    <span class="truncate text-base font-bold text-slate-800">{{ $stats['topName'] ?? 'Sin datos' }}</span>
                </div>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-amber-500 transition-all duration-300" style="width: {{ $stats['topPct'] }}%"></div>
                </div>
            </div>
        </div>

        <!-- Búsqueda y filtros (fijos al hacer scroll) -->
        <div class="sticky top-16 z-20 space-y-2 bg-slate-100 pb-2 pt-2">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="search" class="h-5 w-5" />
                </span>
                <input type="text" x-model="search" placeholder="Buscar por corte, combo o técnica..."
                       class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-10 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-amber-500 focus:ring-amber-500" />
                <button type="button" x-show="search.length" x-cloak @click="search = ''"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-700" title="Limpiar">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                <button type="button" @click="filter = 'all'"
                        class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'all' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                    Todos (<span x-text="services.length"></span>)
                </button>
                <button type="button" @click="filter = 'active'"
                        class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'active' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-emerald-600 ring-1 ring-slate-200 hover:text-emerald-700'">
                    Activos (<span x-text="activeCount"></span>)
                </button>
                <button type="button" @click="filter = 'paused'"
                        class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'paused' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-rose-500 ring-1 ring-slate-200 hover:text-rose-600'">
                    Pausados (<span x-text="pausedCount"></span>)
                </button>
            </div>
        </div>

        <!-- Tarjetas -->
        <div class="flex flex-col gap-3">
            <template x-for="service in filteredServices" :key="service.id">
                <div class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition"
                     :class="service.active ? '' : 'opacity-75'">
                    <!-- Cabecera -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl"
                                  :class="service.active ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-400'">
                                <x-icon name="scissors" class="h-6 w-6" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <h3 class="truncate text-base font-bold text-slate-800" x-text="service.name"></h3>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase"
                                          :class="service.active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                                          x-text="service.active ? 'Activo' : 'Pausado'"></span>
                                </div>
                                <p class="mt-0.5 line-clamp-2 text-xs text-slate-400" x-text="service.description || 'Sin descripción'"></p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <a :href="editBase + '/' + service.id + '/edit'"
                               class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200" title="Editar">
                                <x-icon name="edit" class="h-4 w-4" />
                            </a>
                            <form method="POST" :action="editBase + '/' + service.id" onsubmit="return confirm('¿Eliminar este tipo de corte?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-500 transition hover:bg-rose-100" title="Eliminar">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Precios -->
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Precio cliente</span>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-xl font-extrabold text-slate-800" x-text="'$' + service.price.toFixed(2)"></span>
                                <span class="text-xs text-slate-400">USD</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Equivalente BCV</span>
                            <span class="text-sm font-bold text-slate-700" x-text="formatVes(service.price * rate)"></span>
                        </div>
                    </div>

                    <!-- Split comisión -->
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-1 text-emerald-600">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Barbero (<span x-text="commissionRate"></span>%):
                                <strong class="ml-0.5 text-slate-700" x-text="'$' + barberCut(service).toFixed(2)"></strong>
                            </span>
                            <span class="flex items-center gap-1 text-amber-600">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                Estudio (<span x-text="100 - commissionRate"></span>%):
                                <strong class="ml-0.5 text-slate-700" x-text="'$' + studioCut(service).toFixed(2)"></strong>
                            </span>
                        </div>
                        <div class="flex h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full bg-emerald-500 transition-all duration-300" :style="`width: ${commissionRate}%`"></div>
                            <div class="h-full bg-amber-500 transition-all duration-300" :style="`width: ${100 - commissionRate}%`"></div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="flex items-center gap-2 pt-1">
                        <button type="button" @click="openPrice(service)"
                                class="flex h-11 flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-100 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                            <x-icon name="edit" class="h-4 w-4 text-amber-500" /> Editar precio
                        </button>
                        <button type="button" @click="toggleActive(service)"
                                class="flex h-11 items-center justify-center gap-1.5 rounded-xl px-3.5 text-xs font-semibold transition"
                                :class="service.active ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'">
                            <x-icon name="check" class="h-4 w-4" />
                            <span x-text="service.active ? 'Disponible' : 'Reactivar'"></span>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Sin resultados -->
            <div x-show="filteredServices.length === 0" x-cloak
                 class="flex flex-col items-center justify-center rounded-2xl bg-white px-6 py-12 text-center shadow-sm ring-1 ring-slate-200">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                    <x-icon name="search" class="h-7 w-7" />
                </span>
                <p class="mt-3 text-base font-bold text-slate-800">Servicio no encontrado</p>
                <p class="mt-1 max-w-[260px] text-xs text-slate-400">Verifica el nombre o crea un nuevo tipo de corte para agregarlo al catálogo.</p>
                <button type="button" @click="search = ''; filter = 'all'"
                        class="mt-4 rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-amber-600 transition hover:bg-slate-200">
                    Limpiar filtros
                </button>
            </div>
        </div>

        <!-- Modal editar precio -->
        <div x-show="editing" x-cloak class="fixed inset-0 z-50 flex items-end justify-center">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="editing = null"></div>
            <div class="relative flex w-full max-w-lg flex-col gap-4 rounded-t-3xl bg-white p-5 shadow-2xl">
                <div class="mx-auto h-1.5 w-12 rounded-full bg-slate-200"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Actualizar precio</span>
                        <h3 class="text-lg font-bold text-slate-800" x-text="editing ? editing.name : ''"></h3>
                    </div>
                    <button type="button" @click="editing = null"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500">Precio en dólares (USD)</label>
                    <div class="relative flex items-center">
                        <span class="pointer-events-none absolute left-4 text-lg font-bold text-amber-500">$</span>
                        <input type="number" step="0.01" min="0" x-model.number="priceInput"
                               class="h-14 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-lg font-bold text-slate-800 focus:border-amber-500 focus:ring-amber-500" />
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                    <span class="text-xs text-slate-500">Equivalente BCV</span>
                    <span class="text-sm font-bold text-emerald-600" x-text="formatVes((priceInput || 0) * rate)"></span>
                </div>

                <button type="button" @click="savePrice()"
                        class="h-12 w-full rounded-xl bg-amber-500 text-sm font-bold text-slate-900 shadow-md transition hover:bg-amber-400 active:scale-[0.98]">
                    Guardar nuevo precio
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
