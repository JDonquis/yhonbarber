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
