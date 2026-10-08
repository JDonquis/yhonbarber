@php
    $expense = $expense ?? null;
    $editing = (bool) ($expense && $expense->exists);
    $currentAmount = (float) old('amount_usd', $expense?->amount_usd ?? 0);
    $currentDate = old('expense_date', $expense?->expense_date?->format('Y-m-d') ?? now()->toDateString());
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Gastos</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4" x-data="{
        entry: {{ $currentAmount }},
        currency: 'USD',
        rate: {{ (float) $currentRate }},
        formatVes(value) {
            return 'Bs. ' + Number(value || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        formatUsd(value) {
            return '$' + Number(value || 0).toFixed(2);
        },
        get usdValue() {
            return this.currency === 'VES' ? Number(this.entry || 0) / this.rate : Number(this.entry || 0);
        },
        get equivalent() {
            return this.currency === 'VES' ? this.formatUsd(this.usdValue) : this.formatVes(this.usdValue * this.rate);
        },
        setCurrency(value) {
            if (value === this.currency) return;
            const usd = this.usdValue;
            this.currency = value;
            this.entry = value === 'VES' ? Number((usd * this.rate).toFixed(2)) : Number(usd.toFixed(2));
        }
    }">
        <!-- Subcabecera: volver + tasa -->
        <div class="flex items-center justify-between">
            <a href="{{ route('expenses.index') }}"
               class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-100 active:scale-95" title="Volver a gastos">
                <x-icon name="arrow-left" class="h-5 w-5" />
            </a>
            <div class="flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 shadow-sm ring-1 ring-slate-200">
                <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">BCV:</span>
                <span class="text-[11px] font-bold text-emerald-600">{{ ves($currentRate) }}</span>
            </div>
        </div>

        <!-- Título -->
        <div>
            <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-600">Gastos · {{ $editing ? 'Edición' : 'Registro' }}</span>
            <h2 class="text-xl font-extrabold tracking-tight text-slate-800">{{ $editing ? 'Editar Gasto' : 'Nuevo Gasto' }}</h2>
            <p class="mt-0.5 text-xs text-slate-400">Registra un concepto y su monto para descontarlo de la ganancia de la tienda.</p>
        </div>

        <form method="POST" action="{{ $editing ? route('expenses.update', $expense) : route('expenses.store') }}" class="space-y-4">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <x-card>
                <div class="space-y-5 p-4 sm:p-5">
                    <!-- Concepto -->
                    <div class="space-y-1.5">
                        <x-input-label for="concept" value="Concepto *" />
                        <input id="concept" name="concept" type="text" required autofocus
                               value="{{ old('concept', $expense?->concept) }}"
                               placeholder="Ej. Alquiler, Electricidad, Insumos, Publicidad..."
                               class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                        <x-input-error :messages="$errors->get('concept')" />
                    </div>

                    <!-- Monto -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <x-input-label for="amount" value="Monto *" />
                            <div class="inline-flex rounded-lg bg-slate-100 p-0.5">
                                <button type="button" @click="setCurrency('USD')"
                                        :class="currency === 'USD' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'"
                                        class="rounded-md px-3 py-1 text-xs font-bold uppercase tracking-wide transition">USD</button>
                                <button type="button" @click="setCurrency('VES')"
                                        :class="currency === 'VES' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'"
                                        class="rounded-md px-3 py-1 text-xs font-bold uppercase tracking-wide transition">VES</button>
                            </div>
                        </div>

                        <input type="hidden" name="amount_usd" :value="usdValue.toFixed(2)">

                        <div class="relative flex items-center rounded-xl border border-slate-200 bg-slate-50 px-4 transition focus-within:border-amber-500 focus-within:bg-white">
                            <span class="select-none pr-2 text-lg font-extrabold text-rose-500" x-text="currency === 'VES' ? 'Bs.' : '$'"></span>
                            <input id="amount" type="number" step="0.01" min="0.01" required x-ref="amountInput"
                                   x-model.number="entry"
                                   class="h-14 w-full border-0 bg-transparent p-0 text-xl font-extrabold tracking-tight text-slate-800 focus:ring-0" />
                            <button type="button" @click="entry = 0; $refs.amountInput.focus()"
                                    class="p-2 text-slate-400 transition hover:text-slate-700" title="Limpiar monto">
                                <x-icon name="x" class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Equivalencia -->
                        <div class="mt-1 flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-100">
                            <span class="flex items-center gap-1.5 text-xs text-slate-400">
                                <x-icon name="refresh" class="h-4 w-4 text-emerald-500" /> Equivale a:
                            </span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-bold text-emerald-600" x-text="equivalent"></span>
                                <span class="text-[11px] text-slate-400">(a la tasa actual)</span>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('amount_usd')" />
                    </div>

                    <!-- Fecha -->
                    <div class="space-y-1.5">
                        <x-input-label for="expense_date" value="Fecha del gasto *" />
                        <input id="expense_date" name="expense_date" type="date" required
                               value="{{ $currentDate }}"
                               class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-amber-500 focus:bg-white focus:ring-amber-500" />
                        <x-input-error :messages="$errors->get('expense_date')" />
                    </div>

                    <!-- Notas -->
                    <div class="space-y-1.5">
                        <x-input-label for="notes" value="Notas" />
                        <textarea id="notes" name="notes" rows="3"
                                  placeholder="Detalles adicionales (opcional)..."
                                  class="block w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-amber-500 focus:bg-white focus:ring-amber-500">{{ old('notes', $expense?->notes) }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" />
                    </div>
                </div>
            </x-card>

            <!-- Acciones -->
            <div class="flex flex-col gap-2 pt-1">
                <button type="submit"
                        class="flex h-[54px] w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 text-sm font-extrabold uppercase tracking-wide text-slate-900 shadow-lg shadow-amber-500/30 transition-all hover:opacity-95 active:scale-[0.985]">
                    <x-icon name="check" class="h-5 w-5" />
                    {{ $editing ? 'Guardar cambios' : 'Registrar gasto' }}
                </button>
                <a href="{{ route('expenses.index') }}"
                   class="flex h-12 w-full items-center justify-center rounded-xl bg-white text-sm font-semibold text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-50 active:scale-[0.985]">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
