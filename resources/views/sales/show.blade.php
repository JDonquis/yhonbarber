@php
    $items = $sale->items;
    $isService = $sale->type === \App\Models\Sale::TYPE_SERVICE;
    $studioPct = max(0, 100 - (float) $sale->commission_rate);

    $waText = urlencode(
        "Recibo {$sale->code}\n".
        $items->map(fn ($item) => "{$item->name} x{$item->quantity}: ".usd($item->line_total_usd))->join("\n").
        "\nTotal: ".usd($sale->total_usd).' ('.ves($sale->total_ves).')'
    );
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Detalle de venta</h1>
    </x-slot>

    <div class="max-w-md mx-auto space-y-3.5">
        <!-- Barra superior -->
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1.5 -ml-2 text-xs font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-amber-600 active:scale-95">
                <x-icon name="arrow-left" class="h-4 w-4" /> Historial
            </a>

            @if (auth()->user()->isAdmin() && ! $sale->isCancelled())
                <form method="POST" action="{{ route('sales.cancel', $sale) }}"
                      onsubmit="return confirm('¿Anular esta venta? El stock de productos será restituido.');">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-500 transition hover:bg-rose-100 active:scale-95">
                        <x-icon name="x" class="h-3.5 w-3.5" /> Anular
                    </button>
                </form>
            @endif
        </div>

        <!-- Subtítulo -->
        <div class="flex items-center justify-between px-1">
            <span class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">{{ $shopName }}</span>
            <span class="font-mono text-[11px] font-semibold text-amber-600">{{ $sale->code }}</span>
        </div>

        <!-- Banner de estado -->
        @if ($sale->isCancelled())
            <div class="flex items-center gap-2.5 rounded-xl border border-rose-200 bg-rose-50 p-3 text-rose-600">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-100">
                    <x-icon name="alert" class="h-4 w-4" />
                </span>
                <span class="flex-1 text-xs font-medium leading-tight">Venta anulada. El stock de productos fue restituido.</span>
            </div>
        @else
            <div class="flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-700">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                    <x-icon name="check" class="h-4 w-4" />
                </span>
                <span class="flex-1 text-xs font-medium leading-tight">Venta registrada correctamente en el sistema.</span>
            </div>
        @endif

        <!-- Ticket -->
        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3.5">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                        <x-icon name="receipt" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-bold leading-tight text-slate-800">Ticket {{ $sale->code }}</h2>
                        <p class="text-[11px] text-slate-400">{{ $shopName }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $sale->isCancelled() ? 'border-rose-200 bg-rose-50 text-rose-600' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $sale->isCancelled() ? 'bg-rose-500' : 'animate-pulse bg-emerald-500' }}"></span>
                    {{ $sale->isCancelled() ? 'Anulada' : 'Completada' }}
                </span>
            </div>

            <div class="space-y-4 p-4">
                <!-- Ítems -->
                <div>
                    <div class="mb-2 flex justify-between px-0.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        <span>Servicio / ítem</span>
                        <span>Monto</span>
                    </div>
                    <div class="space-y-2">
                        @foreach ($items as $item)
                            @php($itemService = $item->item_type === \App\Models\SaleItem::TYPE_SERVICE)
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="truncate text-xs font-semibold text-slate-700">{{ $item->name }}</span>
                                        <span class="rounded border px-1.5 py-0.5 text-[10px] font-medium {{ $itemService ? 'border-amber-200 bg-amber-50 text-amber-600' : 'border-emerald-200 bg-emerald-50 text-emerald-600' }}">
                                            {{ $itemService ? 'Servicio' : 'Producto' }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 font-mono text-[11px] text-slate-400">{{ $item->quantity }} × {{ usd($item->unit_price_usd) }}</p>
                                </div>
                                <span class="shrink-0 font-mono text-sm font-bold text-slate-700">{{ usd($item->line_total_usd) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Separador -->
                <div class="relative flex items-center">
                    <div class="flex-grow border-t border-dashed border-slate-200"></div>
                    <span class="mx-2 flex-shrink text-[10px] font-medium uppercase tracking-widest text-slate-400">Resumen financiero</span>
                    <div class="flex-grow border-t border-dashed border-slate-200"></div>
                </div>

                <!-- Total -->
                <div class="flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <div>
                        <span class="block text-xs font-medium text-amber-700">Total a pagar</span>
                        <span class="mt-0.5 block text-[11px] text-slate-400">Equivalente tasa aplicada</span>
                        <span class="font-mono text-[11px] font-medium text-amber-700">{{ ves($sale->total_ves) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-mono text-3xl font-extrabold tracking-tight text-amber-600">{{ usd($sale->total_usd) }}</span>
                    </div>
                </div>

                <!-- Desglose -->
                <div class="space-y-1 pt-1 text-xs">
                    <div class="flex items-center justify-between py-1">
                        <span class="flex items-center gap-2 text-slate-400">
                            <x-icon name="refresh" class="h-4 w-4 shrink-0" /> Tasa aplicada
                        </span>
                        <span class="font-mono font-medium text-slate-600">Bs. {{ number_format($sale->exchange_rate, 2, ',', '.') }} / $1</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 py-1">
                        <span class="flex items-center gap-2 text-slate-400">
                            <x-icon name="users" class="h-4 w-4 shrink-0 text-emerald-500" /> Comisión barbero ({{ rtrim(rtrim(number_format($sale->commission_rate, 2, ',', '.'), '0'), ',') }}%)
                        </span>
                        <span class="font-mono font-bold text-emerald-600">{{ usd($sale->barber_commission_usd) }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 py-1">
                        <span class="flex items-center gap-2 text-slate-400">
                            <x-icon name="dollar" class="h-4 w-4 shrink-0 text-amber-500" /> Monto estudio ({{ rtrim(rtrim(number_format($studioPct, 2, ',', '.'), '0'), ',') }}%)
                        </span>
                        <span class="font-mono font-bold text-amber-600">{{ usd($sale->shop_amount_usd) }}</span>
                    </div>
                </div>

                @if ($sale->notes)
                    <div class="rounded-lg bg-slate-50 px-3 py-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Notas</p>
                        <p class="mt-0.5 text-xs text-slate-600">{{ $sale->notes }}</p>
                    </div>
                @endif
            </div>
        </section>

        <!-- Información de la operación -->
        <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="mb-3 flex items-center gap-2 border-b border-slate-100 pb-2.5">
                <x-icon name="check" class="h-4 w-4 shrink-0 text-amber-500" />
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Información de la operación</h3>
            </div>
            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 p-2">
                    <span class="flex items-center gap-2 text-slate-400"><x-icon name="clock" class="h-4 w-4 shrink-0" /> Fecha &amp; hora</span>
                    <span class="font-mono font-medium text-slate-700">{{ $sale->sold_at->format('d/m/Y h:i a') }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 p-2">
                    <span class="flex items-center gap-2 text-slate-400"><x-icon name="user" class="h-4 w-4 shrink-0" /> Barbero</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        {{ $sale->barber->name ?? 'Venta tienda' }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 p-2">
                    <span class="flex items-center gap-2 text-slate-400"><x-icon name="users" class="h-4 w-4 shrink-0" /> Registrada por</span>
                    <span class="font-medium text-slate-700">{{ $sale->user->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 p-2">
                    <span class="flex items-center gap-2 text-slate-400"><x-icon name="card" class="h-4 w-4 shrink-0" /> Método de pago</span>
                    <span class="inline-flex items-center gap-1.5 font-medium text-slate-700">
                        <span class="h-2 w-2 rounded-full bg-sky-400"></span>
                        {{ $sale->payment_method ?: 'Sin especificar' }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 p-2">
                    <span class="flex items-center gap-2 text-slate-400"><x-icon name="dollar" class="h-4 w-4 shrink-0" /> Moneda de cobro</span>
                    <span class="rounded border border-slate-200 bg-white px-2.5 py-0.5 font-mono text-xs font-bold text-amber-600">{{ $sale->payment_currency }}</span>
                </div>
            </div>
        </section>

        <!-- Acciones -->
        <div class="space-y-2.5 pt-1">
            <a href="{{ route('sales.print', $sale) }}" target="_blank" rel="noopener"
               class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 text-sm font-bold text-slate-900 shadow-lg shadow-amber-500/25 transition-all hover:opacity-95 active:scale-[0.985]">
                <x-icon name="printer" class="h-5 w-5" /> Imprimir comprobante
            </a>
            <a href="https://wa.me/?text={{ $waText }}" target="_blank" rel="noopener noreferrer"
               class="flex h-12 w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white text-xs font-semibold text-emerald-600 shadow-sm transition hover:bg-emerald-50 active:scale-[0.985]">
                <x-icon name="chat" class="h-4 w-4" /> Compartir recibo por WhatsApp
            </a>
        </div>
    </div>
</x-app-layout>
