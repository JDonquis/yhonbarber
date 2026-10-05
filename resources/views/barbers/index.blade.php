@php
    $barbersData = $barbers->map(fn ($barber) => [
        'id' => $barber->id,
        'name' => $barber->name,
        'email' => $barber->email,
        'phone' => $barber->phone,
        'active' => (bool) $barber->active,
    ])->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Barberos</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        barbers: @js($barbersData),
        search: '',
        filter: 'all',
        confirming: null,
        token: '{{ csrf_token() }}',
        editBase: '{{ url('/barbers') }}',
        get filteredBarbers() {
            const q = this.search.toLowerCase().trim();
            return this.barbers.filter((barber) => {
                const matchesSearch = ! q
                    || barber.name.toLowerCase().includes(q)
                    || (barber.email || '').toLowerCase().includes(q)
                    || (barber.phone || '').includes(q);
                const matchesFilter = this.filter === 'all' || (this.filter === 'active' ? barber.active : ! barber.active);
                return matchesSearch && matchesFilter;
            });
        },
        get activeCount() { return this.barbers.filter((barber) => barber.active).length; },
        get inactiveCount() { return this.barbers.filter((barber) => ! barber.active).length; },
        initials(name) {
            return (name || '').trim().split(/\s+/).slice(0, 2).map((word) => word.charAt(0)).join('').toUpperCase();
        },
        waNumber(phone) {
            let digits = (phone || '').replace(/\D/g, '');
            if (! digits) return '';
            if (digits.startsWith('58')) return digits;
            if (digits.startsWith('0')) return '58' + digits.slice(1);
            return digits;
        },
        askToggle(barber) { this.confirming = barber; },
        async confirmToggle() {
            if (! this.confirming) return;
            const body = new FormData();
            body.append('_token', this.token);
            const response = await fetch(this.editBase + '/' + this.confirming.id + '/toggle', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: body,
            });
            if (response.ok) {
                const data = await response.json();
                this.confirming.active = data.active;
            }
            this.confirming = null;
        }
    }">
        <!-- Encabezado -->
        <div class="space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-600">Staff &amp; Permisos</span>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-800">Equipo de Barberos</h2>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <x-icon name="users" class="h-5 w-5" />
                </span>
            </div>
            <p class="text-sm text-slate-400">Gestión y control de acceso de barberos a la suite de trabajo.</p>

            <a href="{{ route('barbers.create') }}"
               class="flex h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-amber-500 text-sm font-bold text-slate-900 shadow-lg shadow-amber-500/25 transition hover:bg-amber-400 active:scale-[0.985]">
                <x-icon name="plus" class="h-5 w-5" /> Nuevo barbero
            </a>
        </div>

        <!-- Búsqueda y filtros (fijos al hacer scroll) -->
        <div class="sticky top-16 z-20 space-y-2 bg-slate-100 pb-2 pt-2">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="search" class="h-5 w-5" />
                </span>
                <input type="search" x-model="search" placeholder="Buscar por nombre, email o tel..."
                       class="h-[50px] w-full rounded-xl border border-slate-200 bg-white pl-11 pr-10 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-amber-500 focus:ring-amber-500" />
                <button type="button" x-show="search.length" x-cloak @click="search = ''"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-700" title="Limpiar">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                <button type="button" @click="filter = 'all'"
                        class="inline-flex h-9 items-center justify-center gap-1.5 whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'all' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                    <span>Todos</span>
                    <span class="rounded-full px-1.5 text-[11px] font-bold" :class="filter === 'all' ? 'bg-slate-900/10' : 'bg-slate-100'" x-text="barbers.length"></span>
                </button>
                <button type="button" @click="filter = 'active'"
                        class="inline-flex h-9 items-center justify-center gap-1.5 whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'active' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                    <span>Activos</span>
                    <span class="rounded-full px-1.5 text-[11px] font-bold text-emerald-600" :class="filter === 'active' ? 'bg-slate-900/10' : 'bg-emerald-50'" x-text="activeCount"></span>
                </button>
                <button type="button" @click="filter = 'inactive'"
                        class="inline-flex h-9 items-center justify-center gap-1.5 whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'inactive' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                    <span>Inactivos</span>
                    <span class="rounded-full px-1.5 text-[11px] font-bold text-rose-500" :class="filter === 'inactive' ? 'bg-slate-900/10' : 'bg-rose-50'" x-text="inactiveCount"></span>
                </button>
            </div>
        </div>

        <!-- Tarjetas -->
        <div class="flex flex-col gap-3">
            <template x-for="barber in filteredBarbers" :key="barber.id">
                <div class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition"
                     :class="barber.active ? '' : 'opacity-80'">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-base font-bold"
                                 :class="barber.active ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-400'">
                                <span x-text="initials(barber.name)"></span>
                                <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full ring-2 ring-white"
                                      :class="barber.active ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-bold text-slate-800" x-text="barber.name"></h3>
                                <p class="truncate text-xs text-slate-400" x-text="barber.email"></p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider"
                              :class="barber.active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600'"
                              x-text="barber.active ? 'Activo' : 'Inactivo'"></span>
                    </div>

                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-2.5">
                        <div class="flex min-w-0 items-center gap-1.5">
                            <x-icon name="phone" class="h-4 w-4 shrink-0 text-amber-500" />
                            <span class="truncate text-xs font-medium text-slate-600" x-text="barber.phone || 'Sin teléfono'"></span>
                        </div>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <template x-if="barber.phone">
                                <a :href="'https://wa.me/' + waNumber(barber.phone)" target="_blank" rel="noopener noreferrer" title="WhatsApp"
                                   class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-emerald-600 ring-1 ring-slate-200 transition hover:bg-emerald-50">
                                    <x-icon name="chat" class="h-4 w-4" />
                                </a>
                            </template>
                            <a :href="editBase + '/' + barber.id + '/edit'" title="Editar"
                               class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-slate-500 ring-1 ring-slate-200 transition hover:text-amber-600">
                                <x-icon name="edit" class="h-4 w-4" />
                            </a>
                            <button type="button" @click="askToggle(barber)" :title="barber.active ? 'Desactivar' : 'Activar'"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200 transition"
                                    :class="barber.active ? 'text-slate-400 hover:text-rose-500' : 'text-emerald-500 hover:text-emerald-600'">
                                <x-icon name="power" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Sin resultados -->
            <div x-show="filteredBarbers.length === 0" x-cloak
                 class="flex flex-col items-center justify-center rounded-2xl bg-white px-6 py-12 text-center shadow-sm ring-1 ring-slate-200">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500">
                    <x-icon name="users" class="h-7 w-7" />
                </span>
                <p class="mt-3 text-base font-bold text-slate-800">Sin coincidencias</p>
                <p class="mt-1 max-w-xs text-xs text-slate-400">No encontramos barberos con ese término de búsqueda o filtro seleccionado.</p>
                <button type="button" @click="search = ''; filter = 'all'"
                        class="mt-4 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-amber-600 transition hover:bg-slate-200">
                    Restablecer filtros
                </button>
            </div>
        </div>

        <!-- Capacidad operativa -->
        <div class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-white to-slate-50 p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <x-icon name="scissors" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-bold text-slate-800">Capacidad operativa</p>
                    <p class="text-xs font-medium text-emerald-600">
                        <span x-text="activeCount"></span> de <span x-text="barbers.length"></span> puestos activos
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[11px] text-slate-400">Turnos hoy</span>
                <p class="text-lg font-extrabold text-amber-600">{{ $todayShifts }}</p>
            </div>
        </div>

        <!-- Confirmación activar/desactivar -->
        <div x-show="confirming" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="confirming = null"></div>
            <div class="relative flex w-full max-w-sm flex-col gap-4 rounded-t-3xl bg-white p-5 text-center shadow-2xl sm:rounded-2xl">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full"
                      :class="confirming && confirming.active ? 'bg-rose-100 text-rose-500' : 'bg-emerald-100 text-emerald-600'">
                    <x-icon name="power" class="h-6 w-6" />
                </span>
                <div>
                    <h3 class="text-lg font-bold text-slate-800"
                        x-text="confirming && confirming.active ? '¿Desactivar barbero?' : '¿Activar barbero?'"></h3>
                    <p class="mt-1 text-sm text-slate-500">
                        <span x-text="confirming && confirming.active ? 'Se suspenderá el acceso de ' : 'Se restaurará el acceso de '"></span>
                        <span class="font-semibold text-slate-700" x-text="confirming ? confirming.name : ''"></span>
                        <span> a la agenda y comisiones del estudio.</span>
                    </p>
                </div>
                <div class="flex flex-col gap-2">
                    <button type="button" @click="confirmToggle()"
                            class="h-12 w-full rounded-xl text-sm font-bold transition active:scale-[0.985]"
                            :class="confirming && confirming.active ? 'bg-rose-100 text-rose-600 hover:bg-rose-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'"
                            x-text="confirming && confirming.active ? 'Sí, desactivar acceso' : 'Sí, activar acceso'"></button>
                    <button type="button" @click="confirming = null"
                            class="h-11 w-full rounded-xl bg-slate-100 text-sm font-semibold text-slate-500 transition hover:bg-slate-200">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
