<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Configuración</h1>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-card title="Tasa del dólar" description="Cotización usada para convertir precios a bolívares">
            <div class="p-6 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-3xl font-bold text-slate-900">{{ ves($currentRate) }}</p>
                        <p class="text-sm text-slate-500">
                            por $1 ·
                            @if (app(\App\Services\ExchangeRateService::class)->usingManual())
                                <span class="font-medium text-amber-600">Tasa manual activa</span>
                            @else
                                Fuente: {{ ucfirst(app(\App\Services\ExchangeRateService::class)->source()) }} (API)
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('exchange-rate.refresh') }}">
                            @csrf
                            <button class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <x-icon name="refresh" class="h-4 w-4" /> Actualizar desde API
                            </button>
                        </form>
                        @if (app(\App\Services\ExchangeRateService::class)->usingManual())
                            <form method="POST" action="{{ route('exchange-rate.clear-manual') }}">
                                @csrf
                                <button class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100">
                                    Quitar tasa manual
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <p class="text-xs text-slate-400">
                    La tasa se actualiza automáticamente cada hora. Si indicas una tasa manual, se usará esa y se ignorará la API.
                </p>
            </div>
        </x-card>

        <x-card>
            <form method="POST" action="{{ route('settings.update') }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <h3 class="text-base font-semibold text-slate-800">Datos de la barbería</h3>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <x-input-label for="shop_name" value="Nombre del negocio" />
                            <x-text-input id="shop_name" name="shop_name" class="mt-1 block w-full" :value="old('shop_name', $values['shop_name'] ?? config('app.name'))" required />
                            <x-input-error :messages="$errors->get('shop_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="shop_rif" value="RIF / Documento" />
                            <x-text-input id="shop_rif" name="shop_rif" class="mt-1 block w-full" :value="old('shop_rif', $values['shop_rif'] ?? '')" />
                        </div>
                        <div>
                            <x-input-label for="shop_phone" value="Teléfono" />
                            <x-text-input id="shop_phone" name="shop_phone" class="mt-1 block w-full" :value="old('shop_phone', $values['shop_phone'] ?? '')" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="shop_address" value="Dirección" />
                            <x-text-input id="shop_address" name="shop_address" class="mt-1 block w-full" :value="old('shop_address', $values['shop_address'] ?? '')" />
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-6">
                    <h3 class="text-base font-semibold text-slate-800">Comisiones</h3>
                    <div class="mt-4 max-w-xs">
                        <x-input-label for="commission_rate" value="Comisión del barbero (%)" />
                        <x-text-input id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="100"
                                      class="mt-1 block w-full" :value="old('commission_rate', $values['commission_rate'] ?? 0)" required />
                        <p class="mt-1 text-xs text-slate-400">La tienda recibe el porcentaje restante. Aplica solo a cortes.</p>
                        <x-input-error :messages="$errors->get('commission_rate')" class="mt-2" />
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-6">
                    <h3 class="text-base font-semibold text-slate-800">Pagos y tasa</h3>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <x-input-label for="payment_methods" value="Métodos de pago" />
                            <x-text-input id="payment_methods" name="payment_methods" class="mt-1 block w-full"
                                          :value="old('payment_methods', $values['payment_methods'] ?? '')" />
                            <p class="mt-1 text-xs text-slate-400">Sepáralos con comas. Ej: Efectivo Bs, Efectivo USD, Pago móvil, Punto de venta, Zelle.</p>
                        </div>
                        <div>
                            <x-input-label for="dolar_api_source" value="Fuente de la tasa" />
                            <select id="dolar_api_source" name="dolar_api_source" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                <option value="oficial" @selected(old('dolar_api_source', $values['dolar_api_source'] ?? 'oficial') === 'oficial')>Oficial (BCV)</option>
                                <option value="paralelo" @selected(old('dolar_api_source', $values['dolar_api_source'] ?? 'oficial') === 'paralelo')>Paralelo</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="exchange_rate_manual" value="Tasa manual (opcional)" />
                            <x-text-input id="exchange_rate_manual" name="exchange_rate_manual" type="number" step="0.0001" min="0"
                                          class="mt-1 block w-full" :value="old('exchange_rate_manual', $values['exchange_rate_manual'] ?? '')" />
                            <p class="mt-1 text-xs text-slate-400">Déjalo vacío para usar la API automáticamente.</p>
                        </div>
                        <div>
                            <x-input-label for="exchange_rate_fallback" value="Tasa de respaldo" />
                            <x-text-input id="exchange_rate_fallback" name="exchange_rate_fallback" type="number" step="0.0001" min="0"
                                          class="mt-1 block w-full" :value="old('exchange_rate_fallback', $values['exchange_rate_fallback'] ?? '')" />
                            <p class="mt-1 text-xs text-slate-400">Se usa si la API falla y no hay historial.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <x-primary-button>Guardar configuración</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
