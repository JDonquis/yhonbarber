@php
    $requestsData = $requests->map(fn ($request) => [
        'id' => $request->id,
        'name' => $request->user?->name ?? 'Usuario eliminado',
        'email' => $request->user?->email ?? '—',
        'status' => $request->status,
        'created_human' => optional($request->created_at)->diffForHumans(),
        'resolved_human' => optional($request->resolved_at)->diffForHumans(),
        'resolved_by' => $request->resolvedBy?->name,
    ])->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Solicitudes de contraseña</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        requests: @js($requestsData),
        search: '',
        filter: 'all',
        generated: null,
        token: '{{ csrf_token() }}',
        base: '{{ url('/password-requests') }}',
        get filteredRequests() {
            const q = this.search.toLowerCase().trim();
            return this.requests.filter((item) => {
                const matchesSearch = ! q
                    || item.name.toLowerCase().includes(q)
                    || (item.email || '').toLowerCase().includes(q);
                const matchesFilter = this.filter === 'all' || item.status === this.filter;
                return matchesSearch && matchesFilter;
            });
        },
        get pendingCount() { return this.requests.filter((item) => item.status === 'pending').length; },
        get resolvedCount() { return this.requests.filter((item) => item.status === 'resolved').length; },
        initials(name) {
            return (name || '').trim().split(/\s+/).slice(0, 2).map((word) => word.charAt(0)).join('').toUpperCase();
        },
        async resolve(item) {
            const body = new FormData();
            body.append('_token', this.token);
            const response = await fetch(this.base + '/' + item.id + '/resolve', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: body,
            });
            if (response.ok) {
                const data = await response.json();
                this.generated = data;
                item.status = 'resolved';
                item.resolved_human = 'ahora mismo';
                this.filter = 'all';
            }
        },
        async cancel(item) {
            if (! confirm('¿Descartar la solicitud de ' + item.name + '?')) return;
            const response = await fetch(this.base + '/' + item.id, {
                method: 'DELETE',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': this.token },
            });
            if (response.ok) {
                item.status = 'resolved';
                item.resolved_human = 'ahora mismo';
                this.filter = 'all';
            }
        },
        copyPassword() {
            if (this.generated && navigator.clipboard) {
                navigator.clipboard.writeText(this.generated.password);
            }
        }
    }">
        <!-- Hero -->
        <section class="space-y-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between">
                <div class="inline-flex items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-600">Seguridad &amp; Acceso</span>
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-700">
                    <x-icon name="clock" class="h-3.5 w-3.5" />
                    <span x-text="pendingCount"></span> Pendiente<span x-show="pendingCount !== 1" x-cloak>s</span>
                </span>
            </div>

            <div>
                <h2 class="text-xl font-extrabold tracking-tight text-slate-800">Solicitudes de contraseña</h2>
                <p class="mt-1 text-xs leading-snug text-slate-400">Usuarios que olvidaron su contraseña y no pueden recibir correo automático.</p>
            </div>

            <!-- Búsqueda -->
            <div class="relative pt-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="search" class="h-5 w-5" />
                </span>
                <input type="search" x-model="search" placeholder="Buscar por usuario o correo..."
                       class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-10 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                <button type="button" x-show="search.length" x-cloak @click="search = ''"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition hover:text-slate-700" title="Limpiar">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <!-- Filtros -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-0.5">
                <button type="button" @click="filter = 'all'"
                        class="inline-flex h-8 shrink-0 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'all' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700'">
                    Todos (<span x-text="requests.length"></span>)
                </button>
                <button type="button" @click="filter = 'pending'"
                        class="inline-flex h-8 shrink-0 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'pending' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-amber-600 ring-1 ring-slate-200 hover:text-amber-700'">
                    Pendientes (<span x-text="pendingCount"></span>)
                </button>
                <button type="button" @click="filter = 'resolved'"
                        class="inline-flex h-8 shrink-0 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95"
                        :class="filter === 'resolved' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-emerald-600 ring-1 ring-slate-200 hover:text-emerald-700'">
                    Atendidas (<span x-text="resolvedCount"></span>)
                </button>
            </div>
        </section>

        <!-- Solicitudes -->
        <section class="flex flex-col gap-3">
            <template x-for="item in filteredRequests" :key="item.id">
                <article class="flex flex-col gap-3.5 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition"
                         :class="item.status === 'resolved' ? 'opacity-90' : ''">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="relative shrink-0">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl text-base font-bold tracking-wider"
                                     :class="item.status === 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-400'"
                                     x-text="initials(item.name)"></div>
                                <template x-if="item.status === 'pending'">
                                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                                        <span class="relative inline-flex h-3 w-3 rounded-full bg-amber-500"></span>
                                    </span>
                                </template>
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-bold text-slate-800" x-text="item.name"></h3>
                                <p class="truncate text-xs text-slate-400" x-text="item.email"></p>
                            </div>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide"
                              :class="item.status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'">
                            <span class="h-2 w-2 rounded-full" :class="item.status === 'pending' ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                            <span x-text="item.status === 'pending' ? 'Pendiente' : 'Resuelta'"></span>
                        </span>
                    </div>

                    <!-- Pendiente: meta -->
                    <template x-if="item.status === 'pending'">
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500">
                            <span class="flex items-center gap-1.5">
                                <x-icon name="clock" class="h-4 w-4 text-amber-500" />
                                Solicitado: <strong class="font-medium text-slate-700" x-text="item.created_human"></strong>
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400">ID #<span x-text="item.id"></span></span>
                        </div>
                    </template>

                    <!-- Resuelta: nota -->
                    <template x-if="item.status === 'resolved'">
                        <div class="flex items-start gap-2 rounded-lg bg-slate-50 px-3 py-2.5 text-xs">
                            <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
                            <div>
                                <p class="font-medium text-slate-700">Contraseña temporal generada</p>
                                <p class="text-slate-400">
                                    Atendida por <span x-text="item.resolved_by || 'administrador'"></span>
                                    <template x-if="item.resolved_human">
                                        <span> · <span x-text="item.resolved_human"></span></span>
                                    </template>
                                </p>
                            </div>
                        </div>
                    </template>

                    <!-- Acciones -->
                    <template x-if="item.status === 'pending'">
                        <div class="grid grid-cols-2 gap-2.5 pt-0.5">
                            <button type="button" @click="cancel(item)"
                                    class="flex min-h-[48px] items-center justify-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2.5 text-sm font-semibold text-rose-500 transition hover:bg-slate-200 active:scale-[0.98]">
                                <x-icon name="x" class="h-4 w-4" /> Cancelar
                            </button>
                            <button type="button" @click="resolve(item)"
                                    class="flex min-h-[48px] items-center justify-center gap-1.5 rounded-xl bg-amber-500 px-3 py-2.5 text-sm font-bold text-slate-900 shadow-md shadow-amber-500/25 transition hover:bg-amber-400 active:scale-[0.98]">
                                <x-icon name="key" class="h-4 w-4" /> Reestablecer
                            </button>
                        </div>
                    </template>
                    <template x-if="item.status === 'resolved'">
                        <div class="flex items-center justify-end pt-0.5">
                            <button type="button" @click="resolve(item)"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 transition hover:text-amber-700">
                                Reenviar <x-icon name="refresh" class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </template>
                </article>
            </template>

            <!-- Sin resultados -->
            <div x-show="filteredRequests.length === 0" x-cloak
                 class="flex flex-col items-center justify-center gap-3 rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-icon name="search" class="h-7 w-7" />
                </span>
                <div>
                    <p class="text-base font-bold text-slate-800">No se encontraron solicitudes</p>
                    <p class="mt-0.5 text-xs text-slate-400">Verifica el nombre o correo ingresado.</p>
                </div>
                <button type="button" @click="search = ''; filter = 'all'"
                        class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-amber-600 transition hover:bg-slate-200">
                    Restablecer filtros
                </button>
            </div>
        </section>

        <!-- Protocolo -->
        <footer class="flex items-start gap-3 rounded-2xl bg-slate-100 p-4 ring-1 ring-slate-200">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                <x-icon name="lock" class="h-5 w-5" />
            </span>
            <div>
                <p class="text-sm font-bold text-slate-800">Protocolo de reseteo seguro</p>
                <p class="mt-0.5 text-xs leading-relaxed text-slate-500">
                    Al presionar <span class="font-semibold text-amber-600">"Reestablecer"</span> se genera una clave temporal
                    aleatoria que se muestra una sola vez. Anótala y entrégala al usuario; podrá cambiarla desde "Mi perfil".
                </p>
            </div>
        </footer>

        <!-- Modal contraseña generada -->
        <div x-show="generated" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="generated = null"></div>
            <div class="relative flex w-full max-w-sm flex-col gap-4 rounded-t-3xl bg-white p-5 shadow-2xl sm:rounded-2xl">
                <div class="text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                        <x-icon name="key" class="h-6 w-6" />
                    </span>
                    <h3 class="mt-3 text-lg font-bold text-slate-800">Nueva contraseña temporal</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Para <span class="font-semibold text-slate-700" x-text="generated ? generated.name : ''"></span>
                    </p>
                </div>
                <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-4">
                    <span class="select-all font-mono text-lg font-bold tracking-widest text-slate-900" x-text="generated ? generated.password : ''"></span>
                    <button type="button" @click="copyPassword()" title="Copiar"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500 ring-1 ring-slate-200 transition hover:text-amber-600">
                        <x-icon name="copy" class="h-4 w-4" />
                    </button>
                </div>
                <p class="text-center text-xs text-amber-600">Por seguridad no se volverá a mostrar.</p>
                <button type="button" @click="generated = null"
                        class="h-12 w-full rounded-xl bg-amber-500 text-sm font-bold text-slate-900 transition hover:bg-amber-400 active:scale-[0.985]">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
