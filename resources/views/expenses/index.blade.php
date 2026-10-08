@php
    $periodLabel = match ($period) {
        'hoy' => 'Hoy',
        'semana' => 'Esta semana',
        'mes' => 'Este mes',
        default => 'Todos',
    };

    $preserve = array_filter([
        'search' => $search,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Gastos</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-4">
        <!-- Encabezado -->
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-widest text-rose-600">
                    <x-icon name="minus" class="h-4 w-4" /> Egresos del estudio
                </span>
                <h2 class="mt-1 text-xl font-extrabold tracking-tight text-slate-800">Gastos</h2>
                <p class="mt-0.5 text-xs leading-snug text-slate-400">Registra los gastos operativos que se descuentan de la ganancia de la tienda.</p>
            </div>
            <a href="{{ route('expenses.create') }}"
               class="flex h-12 shrink-0 items-center justify-center gap-1.5 rounded-xl bg-amber-500 px-4 text-sm font-bold text-slate-900 shadow-md shadow-amber-500/25 transition hover:bg-amber-400 active:scale-95">
                <x-icon name="plus" class="h-4 w-4" /> Nuevo
            </a>
        </div>

        <!-- Resumen -->
        <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2 flex items-center justify-between rounded-xl bg-rose-50 p-4 shadow-sm ring-1 ring-rose-200">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-500 text-white">
                        <x-icon name="minus" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Gastos · {{ $periodLabel }}</p>
                        <p class="text-[11px] text-rose-700">{{ $stats['filteredCount'] }} registro(s)</p>
                    </div>
                </div>
                <span class="shrink-0 text-2xl font-extrabold tracking-tight text-rose-700">{{ usd($stats['filtered']) }}</span>
            </div>

            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <span class="text-xs text-slate-400">Gastos de hoy</span>
                <p class="mt-1 text-lg font-extrabold tracking-tight text-slate-800">{{ usd($stats['today']) }}</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <span class="text-xs text-slate-400">Gastos del mes</span>
                <p class="mt-1 text-lg font-extrabold tracking-tight text-slate-800">{{ usd($stats['month']) }}</p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="space-y-2">
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                @foreach (['hoy' => 'Hoy', 'semana' => 'Semana', 'mes' => 'Mes', 'all' => 'Todos'] as $key => $label)
                    <a href="{{ route('expenses.index', array_merge($preserve, ['period' => $key])) }}"
                       class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full px-3.5 text-xs font-semibold transition active:scale-95
                              {{ $period === $key ? 'bg-amber-500 text-slate-900 shadow-sm' : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:text-slate-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('expenses.index') }}">
                <input type="hidden" name="period" value="{{ $period }}">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="search" class="h-5 w-5" />
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por concepto o nota..."
                           class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-10 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-amber-500 focus:ring-amber-500" />
                    @if ($search)
                        <a href="{{ route('expenses.index', ['period' => $period]) }}"
                           class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-700" title="Limpiar">
                            <x-icon name="x" class="h-4 w-4" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Listado -->
        <section class="space-y-2.5">
            <div class="flex items-center justify-between px-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Gastos registrados</span>
                <span class="text-[11px] font-semibold text-amber-600">Moneda: USD ($)</span>
            </div>

            <div class="space-y-2">
                @forelse ($expenses as $expense)
                    <div class="flex items-start justify-between gap-3 rounded-xl bg-white p-3.5 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                                <x-icon name="minus" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-800">{{ $expense->concept }}</p>
                                <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-400">
                                    <span class="flex items-center gap-1">
                                        <x-icon name="clock" class="h-3.5 w-3.5" />
                                        {{ $expense->expense_date->format('d/m/Y') }}
                                    </span>
                                    @if ($expense->user)
                                        <span class="text-slate-300">•</span>
                                        <span>{{ $expense->user->name }}</span>
                                    @endif
                                </div>
                                @if ($expense->notes)
                                    <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $expense->notes }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1.5">
                            <span class="text-sm font-extrabold text-rose-600">- {{ usd($expense->amount_usd) }}</span>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('expenses.edit', $expense) }}"
                                   class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition hover:bg-slate-200" title="Editar">
                                    <x-icon name="edit" class="h-4 w-4" />
                                </a>
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('¿Eliminar este gasto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-500 transition hover:bg-rose-100" title="Eliminar">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center rounded-xl bg-white px-6 py-12 text-center shadow-sm ring-1 ring-slate-200">
                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-rose-50 text-rose-500">
                            <x-icon name="minus" class="h-7 w-7" />
                        </span>
                        <p class="mt-3 text-base font-bold text-slate-800">Sin gastos</p>
                        <p class="mt-1 max-w-xs text-xs text-slate-400">No hay gastos registrados para este filtro. Registra uno con el botón Nuevo.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Paginación -->
        @if ($expenses->hasPages())
            <div class="flex flex-col items-center gap-2 pb-2">
                <span class="text-xs text-slate-400">
                    Mostrando {{ $expenses->firstItem() }}-{{ $expenses->lastItem() }} de {{ $expenses->total() }} gastos
                </span>
                @if ($expenses->hasMorePages())
                    <a href="{{ $expenses->nextPageUrl() }}"
                       class="flex w-full items-center justify-center gap-2 rounded-xl bg-white py-3 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">
                        <x-icon name="chevron-down" class="h-4 w-4 text-amber-500" />
                        Cargar gastos anteriores
                    </a>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
