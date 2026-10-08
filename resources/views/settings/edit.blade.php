@php
    $rateService = app(\App\Services\ExchangeRateService::class);
    $usingManual = $rateService->usingManual();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Configuración</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        commission: {{ (float) old('commission_rate', $values['commission_rate'] ?? 0) }},
        methods: @js(old('payment_methods', $values['payment_methods'] ?? '')),
        barberList: @js($barbersData),
        barberSelected: @js(old('barber_id')),
        barberRate: @js(old('barber_commission_rate')),
        barberUseBase: {{ old('barber_use_base') ? 'true' : 'false' }},
        get selectedBarber() { return this.barberList.find((barber) => String(barber.id) === String(this.barberSelected)); },
        selectBarberCommission(id) {
            this.barberSelected = String(id);
            const barber = this.selectedBarber;
            const custom = barber ? !! barber.custom : false;
            this.barberUseBase = ! custom;
            this.barberRate = custom ? Number(barber.rate) : '';
        },
        get methodList() {
            return (this.methods || '').split(',').map((method) => method.trim()).filter(Boolean);
        }
    }">
        <!-- Encabezado -->
        <div>
            <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-widest text-amber-600">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span> Panel operativo
            </span>
            <h2 class="mt-1 text-xl font-extrabold tracking-tight text-slate-800">Configuración del Estudio</h2>
            <p class="mt-0.5 text-xs leading-relaxed text-slate-400">Parámetros de facturación multi-moneda, reparto de comisiones y ficha fiscal comercial.</p>
        </div>

        <!-- Tasa del dólar -->
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                        <x-icon name="dollar" class="h-5 w-5" />
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Tasa del dólar oficial</h3>
                        <p class="text-xs text-slate-400">Conversión automática en caja</p>
                    </div>
                </div>
                @if ($usingManual)
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Manual
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span> En línea
                    </span>
                @endif
            </div>

            <div class="mt-4 rounded-xl bg-slate-50 p-4">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold tracking-tight text-amber-600">{{ ves($currentRate) }}</span>
                    <span class="text-xs text-slate-400">/ 1,00 USD</span>
                </div>
                <p class="mt-2 flex items-center gap-1 text-xs text-slate-400">
                    <x-icon name="home" class="h-4 w-4 text-amber-500" />
                    @if ($usingManual)
                        Tasa manual activa (se ignora la API)
                    @else
                        Fuente: {{ ucfirst($rateService->source()) }} · Banco Central de Venezuela (API)
                    @endif
                </p>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('exchange-rate.refresh') }}">
                    @csrf
                    <button type="submit" class="inline-flex h-11 items-center gap-2 rounded-xl bg-slate-100 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 active:scale-[0.985]">
                        <x-icon name="refresh" class="h-4 w-4 text-amber-500" /> Actualizar desde API oficial
                    </button>
                </form>
                @if ($usingManual)
                    <form method="POST" action="{{ route('exchange-rate.clear-manual') }}">
                        @csrf
                        <button type="submit" class="inline-flex h-11 items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 active:scale-[0.985]">
                            Quitar tasa manual
                        </button>
                    </form>
                @endif
            </div>

            <p class="mt-3 text-[11px] leading-tight text-slate-400">
                Sincronización horaria activa. Si defines una tasa manual, el motor de cobro suspende temporalmente la API.
            </p>
        </section>

        <form method="POST" action="{{ route('settings.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Ficha del establecimiento -->
            <section class="space-y-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                        <x-icon name="home" class="h-5 w-5" />
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Ficha del establecimiento</h3>
                        <p class="text-xs text-slate-400">Información impresa en tickets y facturas</p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <x-input-label for="shop_name" value="Nombre comercial *" />
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"><x-icon name="user" class="h-4 w-4" /></span>
                        <input id="shop_name" name="shop_name" type="text" required
                               value="{{ old('shop_name', $values['shop_name'] ?? config('app.name')) }}"
                               class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                    </div>
                    <x-input-error :messages="$errors->get('shop_name')" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <x-input-label for="shop_rif" value="RIF / Registro fiscal" />
                        <input id="shop_rif" name="shop_rif" type="text"
                               value="{{ old('shop_rif', $values['shop_rif'] ?? '') }}"
                               placeholder="J-00000000-0"
                               class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                    </div>
                    <div class="space-y-1.5">
                        <x-input-label for="shop_phone" value="Teléfono / WhatsApp" />
                        <input id="shop_phone" name="shop_phone" type="tel"
                               value="{{ old('shop_phone', $values['shop_phone'] ?? '') }}"
                               placeholder="+58 412 000 0000"
                               class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <x-input-label for="shop_address" value="Dirección física" />
                    <textarea id="shop_address" name="shop_address" rows="2"
                              placeholder="Av. Principal, local..."
                              class="block w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500">{{ old('shop_address', $values['shop_address'] ?? '') }}</textarea>
                </div>
            </section>

            <!-- Reglas de comisiones -->
            <section class="space-y-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <x-icon name="chart" class="h-5 w-5" />
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Reglas de comisiones</h3>
                        <p class="text-xs text-slate-400">División de ingresos por corte y estilismo</p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <x-input-label for="commission_rate" value="Comisión base del barbero (%)" />
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-amber-600">Automático</span>
                    </div>
                    <div class="relative">
                        <input id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="100" required
                               x-model.number="commission"
                               class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 pr-12 text-right text-lg font-bold text-slate-800 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-lg font-bold text-amber-500">%</span>
                    </div>
                    <x-input-error :messages="$errors->get('commission_rate')" />
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1 font-medium text-emerald-600">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Barbero: <span x-text="commission"></span>%
                        </span>
                        <span class="flex items-center gap-1 font-medium text-amber-600">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                            Estudio: <span x-text="(100 - commission).toFixed(0)"></span>%
                        </span>
                    </div>
                    <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100 p-0.5">
                        <div class="h-full rounded-l-full bg-emerald-500 transition-all duration-300" :style="`width: ${commission}%`"></div>
                        <div class="ml-0.5 h-full rounded-r-full bg-amber-500 transition-all duration-300" :style="`width: ${100 - commission}%`"></div>
                    </div>
                </div>

                <p class="text-[11px] leading-relaxed text-slate-400">
                    La tienda absorbe insumos e infraestructura reteniendo el porcentaje restante. Aplica solo a tarifas de servicios, no a venta de productos retail.
                </p>

                <div class="h-px w-full bg-slate-100"></div>

                <!-- Comisión personalizada por barbero -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <x-icon name="users" class="h-4 w-4 text-emerald-500" /> Comisiones por barbero
                        </span>
                        <span class="text-[11px] text-slate-400">Se guarda con la configuración</span>
                    </div>

                    @if ($barbersData->isEmpty())
                        <p class="rounded-xl bg-slate-50 px-4 py-6 text-center text-xs text-slate-400">No hay barberos registrados.</p>
                    @else
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <x-input-label for="barber_id" value="Barbero" />
                                <span class="text-[11px] text-slate-400">Toca para seleccionar</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                                @foreach ($barbersData as $barber)
                                    @php
                                        $initials = collect(explode(' ', trim($barber['name'])))
                                            ->filter()
                                            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
                                            ->take(2)
                                            ->implode('');
                                    @endphp
                                    <button type="button" @click="selectBarberCommission({{ $barber['id'] }})"
                                            :class="barberSelected === '{{ $barber['id'] }}' ? 'ring-2 ring-amber-500 bg-amber-50' : 'bg-slate-50 hover:bg-slate-100'"
                                            class="flex flex-col items-center gap-1.5 rounded-xl p-2.5 transition">
                                        <span class="flex h-11 w-11 items-center justify-center rounded-full text-sm font-bold transition"
                                              :class="barberSelected === '{{ $barber['id'] }}' ? 'bg-amber-500 text-slate-900' : 'bg-slate-200 text-slate-600'">
                                            {{ $initials }}
                                        </span>
                                        <span class="w-full truncate text-center text-xs font-medium transition"
                                              :class="barberSelected === '{{ $barber['id'] }}' ? 'text-amber-600 font-semibold' : 'text-slate-600'">
                                            {{ $barber['name'] }}
                                        </span>
                                        <span class="rounded-full px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide {{ $barber['custom'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }}">
                                            {{ $barber['custom'] ? 'Personalizada' : 'Base' }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="barber_id" :value="barberSelected">
                            <x-input-error :messages="$errors->get('barber_id')" />
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <x-input-label for="barber_commission_rate" value="Comisión de este barbero (%)" />
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">
                                    Base: {{ rtrim(rtrim(number_format($baseCommission, 2), '0'), '.') }}%
                                </span>
                            </div>
                            <div class="relative">
                                <input id="barber_commission_rate" name="barber_commission_rate" type="number" step="0.01" min="0" max="100"
                                       x-model="barberRate" :disabled="barberUseBase"
                                       placeholder="{{ rtrim(rtrim(number_format($baseCommission, 2), '0'), '.') }}"
                                       class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 pr-12 text-right text-lg font-bold text-slate-800 focus:border-amber-500 focus:bg-white focus:ring-amber-500 disabled:opacity-50" />
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-lg font-bold text-emerald-500">%</span>
                            </div>
                            <label class="flex cursor-pointer items-center gap-2 text-xs text-slate-500">
                                <input type="checkbox" name="barber_use_base" value="1" x-model="barberUseBase"
                                       class="rounded border-slate-300 text-emerald-500 shadow-sm focus:ring-emerald-500">
                                Usar la comisión base del estudio
                            </label>
                            <x-input-error :messages="$errors->get('barber_commission_rate')" />
                        </div>

                        <div class="space-y-1.5 border-t border-slate-100 pt-3">
                            @foreach ($barbersData as $barber)
                                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                                    <span class="truncate text-sm text-slate-700">{{ $barber['name'] }}</span>
                                    <span class="flex items-center gap-2">
                                        <span class="text-sm font-bold {{ $barber['custom'] ? 'text-emerald-600' : 'text-slate-500' }}">
                                            {{ rtrim(rtrim(number_format($barber['rate'], 2), '0'), '.') }}%
                                        </span>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $barber['custom'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }}">
                                            {{ $barber['custom'] ? 'Personalizada' : 'Base' }}
                                        </span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            <!-- Canales de pago y tasa -->
            <section class="space-y-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <x-icon name="card" class="h-5 w-5" />
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Canales de pago y tasa</h3>
                        <p class="text-xs text-slate-400">Métodos autorizados y respaldo operativo</p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <x-input-label for="payment_methods" value="Métodos habilitados para clientes" />
                    <input id="payment_methods" name="payment_methods" type="text" x-model="methods"
                           placeholder="Efectivo Bs, Efectivo USD, Pago móvil, Punto de venta, Zelle"
                           class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                    <div class="flex flex-wrap gap-1.5 rounded-xl bg-slate-50 p-2.5">
                        <template x-for="method in methodList" :key="method">
                            <span class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 text-xs text-slate-700 ring-1 ring-slate-200">
                                <x-icon name="check" class="h-3.5 w-3.5 text-emerald-500" />
                                <span x-text="method"></span>
                            </span>
                        </template>
                        <template x-if="methodList.length === 0">
                            <span class="px-1 text-xs text-slate-400">Sin métodos configurados.</span>
                        </template>
                    </div>
                    <p class="text-[11px] text-slate-400">Sepáralos con comas; se usan en el sistema POS.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <x-input-label for="dolar_api_source" value="Proveedor de tasa" />
                        <select id="dolar_api_source" name="dolar_api_source"
                                class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500">
                            <option value="oficial" @selected(old('dolar_api_source', $values['dolar_api_source'] ?? 'oficial') === 'oficial')>Oficial (BCV)</option>
                            <option value="paralelo" @selected(old('dolar_api_source', $values['dolar_api_source'] ?? 'oficial') === 'paralelo')>Paralelo</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <x-input-label for="exchange_rate_fallback" value="Tasa de contingencia" />
                        <input id="exchange_rate_fallback" name="exchange_rate_fallback" type="number" step="0.0001" min="0"
                               value="{{ old('exchange_rate_fallback', $values['exchange_rate_fallback'] ?? '') }}"
                               placeholder="850.00"
                               class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                        <p class="text-[11px] text-slate-400">Se usa si la API falla y no hay historial.</p>
                    </div>
                    <div class="space-y-1.5 sm:col-span-2">
                        <x-input-label for="exchange_rate_manual" value="Tasa manual (opcional)" />
                        <input id="exchange_rate_manual" name="exchange_rate_manual" type="number" step="0.0001" min="0"
                               value="{{ old('exchange_rate_manual', $values['exchange_rate_manual'] ?? '') }}"
                               placeholder="Déjalo vacío para usar la API"
                               class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                    </div>
                </div>
            </section>

            <!-- Guardar -->
            <div class="pt-1">
                <button type="submit"
                        class="flex h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 text-sm font-extrabold uppercase tracking-wider text-slate-900 shadow-lg shadow-amber-500/30 transition-all hover:opacity-95 active:scale-[0.985]">
                    <x-icon name="check" class="h-5 w-5" /> Guardar configuración
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
