<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Registrar corte</h1>
    </x-slot>

    <div class="max-w-4xl mx-auto" x-data="{
        serviceId: '',
        price: 0,
        rate: {{ (float) $currentRate }},
        services: @js($services->mapWithKeys(fn ($s) => [$s->id => (float) $s->price])),
        update() { this.price = this.services[this.serviceId] ?? 0; },
        get totalVes() { return (this.price * this.rate).toFixed(2); }
    }">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card class="lg:col-span-2" title="Datos del corte">
                <form method="POST" action="{{ route('sales.store-service') }}" class="p-6 space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="service_id" value="Tipo de corte" />
                        <select id="service_id" name="service_id" x-model="serviceId" @change="update()" required
                                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="">Selecciona un corte</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }} — {{ usd($service->price) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('service_id')" class="mt-2" />
                    </div>

                    @if (auth()->user()->isBarber())
                        <input type="hidden" name="barber_id" value="{{ auth()->id() }}">
                        <div>
                            <x-input-label value="Barbero" />
                            <p class="mt-1 rounded-lg bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700">{{ auth()->user()->name }}</p>
                        </div>
                    @else
                        <div>
                            <x-input-label for="barber_id" value="Barbero que realizó el corte" />
                            <select id="barber_id" name="barber_id" required
                                    class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                <option value="">Selecciona el barbero</option>
                                @foreach ($barbers as $barber)
                                    <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('barber_id')" class="mt-2" />
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="payment_method" value="Método de pago" />
                            <select id="payment_method" name="payment_method"
                                    class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                <option value="">Selecciona</option>
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method }}">{{ $method }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="payment_currency" value="Moneda de cobro" />
                            <select id="payment_currency" name="payment_currency"
                                    class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                <option value="USD">Dólares (USD)</option>
                                <option value="VES">Bolívares (VES)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Notas (opcional)" />
                        <x-text-input id="notes" name="notes" class="mt-1 block w-full" :value="old('notes')" />
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                        <a href="{{ route('sales.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                        <x-primary-button>Registrar corte</x-primary-button>
                    </div>
                </form>
            </x-card>

            <x-card class="lg:col-span-1" title="Resumen">
                <div class="p-6 space-y-4">
                    <div class="flex items-baseline justify-between">
                        <span class="text-sm text-slate-500">Precio</span>
                        <span class="text-2xl font-bold text-slate-900" x-text="'$ ' + (price).toFixed(2)">$ 0,00</span>
                    </div>
                    <div class="flex items-baseline justify-between border-t border-slate-100 pt-3">
                        <span class="text-sm text-slate-500">En bolívares</span>
                        <span class="font-semibold text-slate-700">Bs. <span x-text="totalVes">0,00</span></span>
                    </div>
                    <p class="text-xs text-slate-400">Tasa aplicada: Bs. {{ number_format($currentRate, 2, ',', '.') }} por $1.</p>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
