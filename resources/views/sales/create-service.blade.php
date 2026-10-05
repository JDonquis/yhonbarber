<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Registrar corte</h1>
    </x-slot>

    @php
        $methodIcon = function (string $method) {
            $m = mb_strtolower($method);
            return match (true) {
                str_contains($m, 'móvil'), str_contains($m, 'movil'), str_contains($m, 'transfer') => 'refresh',
                str_contains($m, 'punto'), str_contains($m, 'débito'), str_contains($m, 'debito') => 'cart',
                default => 'dollar',
            };
        };
    @endphp

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        serviceId: '{{ old('service_id') }}',
        barberId: '{{ old('barber_id', auth()->user()->isBarber() ? auth()->id() : '') }}',
        amountEntry: {{ (float) old('price', 0) }},
        paymentCurrency: '{{ old('payment_currency', 'USD') }}',
        paymentMethod: '{{ old('payment_method', '') }}',
        updateServicePrice: false,
        rate: {{ (float) $currentRate }},
        commissionRate: {{ $commissionRate }},
        services: @js($services->mapWithKeys(fn ($s) => [$s->id => ['price' => (float) $s->price, 'name' => $s->name]])),
        init() {
            if (this.amountEntry) {
                if (this.paymentCurrency === 'VES') {
                    this.amountEntry = +(this.amountEntry * this.rate).toFixed(2);
                }
            } else if (this.serviceId) {
                this.amountEntry = this.toDisplay(this.servicePrice(this.serviceId));
            }
        },
        servicePrice(id) { return this.services[id]?.price ?? 0; },
        toDisplay(usd) {
            return this.paymentCurrency === 'VES'
                ? +(Number(usd) * this.rate).toFixed(2)
                : +Number(usd).toFixed(2);
        },
        selectService(id) {
            this.serviceId = String(id);
            this.amountEntry = this.toDisplay(this.servicePrice(id));
            this.updateServicePrice = false;
        },
        selectBarber(id) { this.barberId = String(id); },
        setCurrency(currency) {
            if (currency === this.paymentCurrency) return;
            const usd = this.usdPrice;
            this.paymentCurrency = currency;
            this.amountEntry = this.toDisplay(usd);
        },
        adjust(delta) {
            const step = this.paymentCurrency === 'VES' ? delta * this.rate : delta;
            this.amountEntry = Math.max(0, +(Number(this.amountEntry || 0) + step).toFixed(2));
        },
        formatVes(value) {
            return 'Bs. ' + Number(value || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        get usdPrice() { return this.paymentCurrency === 'VES' ? Number(this.amountEntry || 0) / this.rate : Number(this.amountEntry || 0); },
        get hasService() { return this.serviceId !== '' && this.serviceId !== null; },
        get basePrice() { return this.servicePrice(this.serviceId); },
        get serviceName() { return this.services[this.serviceId]?.name ?? ''; },
        get priceChanged() { return this.hasService && Math.abs(this.usdPrice - this.basePrice) > 0.001; },
        get barberUsd() { return this.usdPrice * this.commissionRate / 100; },
        get studioUsd() { return this.usdPrice - this.barberUsd; }
    }">
        <form method="POST" action="{{ route('sales.store-service') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="service_id" :value="serviceId">
            <input type="hidden" name="barber_id" :value="barberId">
            <input type="hidden" name="price" :value="usdPrice.toFixed(2)">
            <input type="hidden" name="payment_method" :value="paymentMethod">
            <input type="hidden" name="payment_currency" :value="paymentCurrency">

            <!-- Barra superior: turno + tasa -->
            <div class="flex items-center justify-between">
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-amber-600">Turno activo</span>
                    <p class="text-lg font-bold text-slate-800">Registrar Corte</p>
                </div>
                <div class="flex items-center gap-2 rounded-full bg-white px-3 py-1.5 shadow-sm ring-1 ring-slate-200">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                    <div class="text-right leading-tight">
                        <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Tasa hoy</span>
                        <span class="text-xs font-bold text-slate-700">{{ ves($currentRate) }}</span>
                    </div>
                </div>
            </div>

            <!-- 1. Barbero -->
            <x-card>
                <div class="p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">1. Seleccionar barbero</span>
                        <span class="flex items-center gap-1 text-xs font-semibold text-amber-600">
                            <x-icon name="users" class="h-4 w-4" /> En silla
                        </span>
                    </div>

                    @if (auth()->user()->isBarber())
                        <div class="flex items-center gap-3 rounded-xl bg-amber-50 p-3 ring-1 ring-amber-200">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500 text-sm font-bold text-slate-900">
                                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-amber-600">Registrando tu propio corte</p>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                            @foreach ($barbers as $barber)
                                @php
                                    $initials = collect(explode(' ', trim($barber->name)))
                                        ->filter()
                                        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
                                        ->take(2)
                                        ->implode('');
                                @endphp
                                <button type="button" @click="selectBarber({{ $barber->id }})"
                                        :class="barberId === '{{ $barber->id }}' ? 'ring-2 ring-amber-500 bg-amber-50' : 'bg-slate-50 hover:bg-slate-100'"
                                        class="flex flex-col items-center gap-1.5 rounded-xl p-2.5 transition">
                                    <span class="flex h-11 w-11 items-center justify-center rounded-full text-sm font-bold transition"
                                          :class="barberId === '{{ $barber->id }}' ? 'bg-amber-500 text-slate-900' : 'bg-slate-200 text-slate-600'">
                                        {{ $initials }}
                                    </span>
                                    <span class="w-full truncate text-center text-xs font-medium transition"
                                          :class="barberId === '{{ $barber->id }}' ? 'text-amber-600 font-semibold' : 'text-slate-600'">
                                        {{ $barber->name }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('barber_id')" />
                    @endif
                </div>
            </x-card>

            <!-- 2. Servicio -->
            <x-card>
                <div class="p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">2. Servicio realizado</span>
                        <span class="text-xs text-slate-400">Toca para fijar</span>
                    </div>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach ($services as $service)
                            <button type="button" @click="selectService({{ $service->id }})"
                                    :class="serviceId === '{{ $service->id }}' ? 'ring-2 ring-amber-500 bg-amber-50' : 'bg-slate-50 hover:bg-slate-100'"
                                    class="flex items-center justify-between gap-2 rounded-xl p-3 text-left transition">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition"
                                          :class="serviceId === '{{ $service->id }}' ? 'bg-amber-500 text-slate-900' : 'bg-slate-200 text-slate-500'">
                                        <x-icon name="scissors" class="h-4 w-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-700">{{ $service->name }}</p>
                                        @if ($service->description)
                                            <p class="truncate text-xs text-slate-400">{{ $service->description }}</p>
                                        @endif
                                    </div>
                                </div>
                                <span class="ml-1 shrink-0 text-sm font-bold text-slate-700">{{ usd($service->price) }}</span>
                            </button>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('service_id')" />
                </div>
            </x-card>

            <!-- 3. Monto -->
            <x-card>
                <div class="p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">3. Monto a cobrar</span>
                        <div class="inline-flex rounded-lg bg-slate-100 p-0.5">
                            <button type="button" @click="setCurrency('USD')"
                                    :class="paymentCurrency === 'USD' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'"
                                    class="rounded-md px-3 py-1 text-xs font-bold uppercase tracking-wide transition">USD</button>
                            <button type="button" @click="setCurrency('VES')"
                                    :class="paymentCurrency === 'VES' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'"
                                    class="rounded-md px-3 py-1 text-xs font-bold uppercase tracking-wide transition">VES</button>
                        </div>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-4 text-center">
                        <div class="flex items-baseline justify-center gap-1">
                            <span class="text-2xl font-extrabold text-amber-500" x-text="paymentCurrency === 'VES' ? 'Bs.' : '$'"></span>
                            <input id="price" type="number" step="0.01" min="0.01" inputmode="decimal"
                                   x-model.number="amountEntry"
                                   class="w-40 border-0 bg-transparent text-center text-3xl font-black text-slate-900 focus:ring-0" />
                            <span class="text-xs font-bold text-slate-400" x-text="paymentCurrency"></span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            Equivalente:
                            <span class="font-semibold text-emerald-600"
                                  x-text="paymentCurrency === 'VES' ? '$' + usdPrice.toFixed(2) : formatVes(usdPrice * rate)"></span>
                        </p>
                        <p class="mt-1 text-xs text-slate-400" x-show="hasService" x-cloak>
                            Precio configurado: <span x-text="'$' + basePrice.toFixed(2)"></span>
                        </p>
                    </div>
                    <x-input-error :messages="$errors->get('price')" />
                    <p class="text-xs text-slate-400">El total se guarda en dólares.</p>

                    <div class="flex items-center gap-2 pt-0.5">
                        <button type="button" @click="adjust(1)" class="flex-1 rounded-lg bg-slate-100 py-2 text-[13px] font-bold text-slate-700 transition hover:bg-slate-200 active:scale-95">+$1</button>
                        <button type="button" @click="adjust(2)" class="flex-1 rounded-lg bg-slate-100 py-2 text-[13px] font-bold text-slate-700 transition hover:bg-slate-200 active:scale-95">+$2</button>
                        <button type="button" @click="adjust(5)" class="flex-1 rounded-lg bg-slate-100 py-2 text-[13px] font-bold text-slate-700 transition hover:bg-slate-200 active:scale-95">+$5</button>
                        <button type="button" @click="adjust(-1)" class="flex w-11 items-center justify-center rounded-lg bg-slate-100 py-2 text-slate-500 transition hover:bg-slate-200 active:scale-95">
                            <x-icon name="minus" class="h-4 w-4" />
                        </button>
                    </div>

                    @if (auth()->user()->isAdmin())
                        <div x-show="priceChanged" x-cloak class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                            <label class="flex cursor-pointer items-start gap-2.5">
                                <input type="checkbox" name="update_service_price" value="1" x-model="updateServicePrice"
                                       class="mt-0.5 rounded border-slate-300 text-amber-500 shadow-sm focus:ring-amber-500">
                                <span class="text-sm text-amber-900">
                                    Guardar $<span class="font-semibold" x-text="usdPrice.toFixed(2)"></span> como el nuevo precio de
                                    "<span class="font-semibold" x-text="serviceName"></span>" para futuros cortes.
                                    <span class="mt-0.5 block text-xs text-amber-700">
                                        Si no lo marcas, el precio nuevo se aplicará solo a este registro.
                                    </span>
                                </span>
                            </label>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Desglose de comisión -->
            <x-card>
                <div class="p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <x-icon name="chart" class="h-4 w-4 text-amber-500" />
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Desglose de comisión</span>
                        </div>
                        <span class="text-[11px] font-medium text-slate-400">Regla: {{ (int) (100 - $commissionRate) }}% / {{ (int) $commissionRate }}%</span>
                    </div>

                    <div class="flex h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full bg-amber-500 transition-all duration-300" :style="`width:${100 - commissionRate}%`"></div>
                        <div class="h-full bg-emerald-500 transition-all duration-300" :style="`width:${commissionRate}%`"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-lg bg-slate-50 p-2.5">
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Estudio ({{ (int) (100 - $commissionRate) }}%)</span>
                                <x-icon name="home" class="h-3.5 w-3.5 text-amber-500" />
                            </div>
                            <p class="text-[17px] font-extrabold text-slate-800" x-text="'$' + studioUsd.toFixed(2)">$0.00</p>
                            <p class="text-[11px] text-slate-400" x-text="formatVes(studioUsd * rate)">Bs. 0,00</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-2.5">
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Barbero ({{ (int) $commissionRate }}%)</span>
                                <x-icon name="scissors" class="h-3.5 w-3.5 text-emerald-500" />
                            </div>
                            <p class="text-[17px] font-extrabold text-slate-800" x-text="'$' + barberUsd.toFixed(2)">$0.00</p>
                            <p class="text-[11px] text-slate-400" x-text="formatVes(barberUsd * rate)">Bs. 0,00</p>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- 4. Pago -->
            <x-card>
                <div class="p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">4. Método de pago</span>
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

                    <details class="group pt-1">
                        <summary class="flex cursor-pointer list-none items-center justify-between py-1 text-sm text-slate-500 hover:text-slate-700">
                            <span class="flex items-center gap-1.5">
                                <x-icon name="edit" class="h-4 w-4" /> Añadir notas o cliente (opcional)
                            </span>
                            <x-icon name="chevron-down" class="h-4 w-4 transition-transform group-open:rotate-180" />
                        </summary>
                        <div class="pt-2">
                            <x-text-input id="notes" name="notes" class="block w-full" :value="old('notes')" placeholder="Ej: Cliente Carlos — barba perfilada" />
                        </div>
                    </details>
                </div>
            </x-card>

            <!-- CTA -->
            <div class="space-y-2 pt-1">
                <button type="submit"
                        class="flex w-full min-h-[52px] items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 px-6 text-sm font-extrabold uppercase tracking-wide text-slate-900 shadow-lg shadow-amber-500/30 transition-all hover:opacity-95 active:scale-[0.985]">
                    <x-icon name="check" class="h-5 w-5" />
                    <span>Confirmar y registrar ($<span x-text="usdPrice.toFixed(2)"></span>)</span>
                </button>
                <a href="{{ route('sales.index') }}" class="flex items-center justify-center gap-1 py-1 text-sm text-slate-400 transition hover:text-slate-600">
                    <x-icon name="x" class="h-4 w-4" /> Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
