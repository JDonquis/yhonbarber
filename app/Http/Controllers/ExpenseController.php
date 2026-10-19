<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        [$period, $search, $from, $to, $rangeStart, $rangeEnd] = $this->filters($request);

        $query = $this->filteredQuery($search, $rangeStart, $rangeEnd);

        $expenses = (clone $query)
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = $this->stats($query);
        $periodLabel = $this->periodLabel($period, $from, $to);

        return view('expenses.index', compact('expenses', 'stats', 'period', 'search', 'from', 'to', 'periodLabel'));
    }

    /**
     * Expense results for the filters without reloading the page.
     */
    public function filter(Request $request)
    {
        [$period, $search, $from, $to, $rangeStart, $rangeEnd] = $this->filters($request);

        $query = $this->filteredQuery($search, $rangeStart, $rangeEnd);

        $expenses = (clone $query)
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(20);

        $expenses->withPath(route('expenses.index'))->appends(array_filter([
            'period' => $period,
            'search' => $search,
            'from' => $period === 'rango' ? $from : null,
            'to' => $period === 'rango' ? $to : null,
        ], fn ($value) => $value !== null && $value !== ''));

        $stats = $this->stats($query);

        return response()->json([
            'html' => view('expenses._list', compact('expenses'))->render(),
            'label' => $this->periodLabel($period, $from, $to),
            'filtered' => usd($stats['filtered']),
            'filtered_count' => $stats['filteredCount'],
            'period' => $period,
            'from' => $from,
            'to' => $to,
        ]);
    }

    protected function filteredQuery(?string $search, ?string $rangeStart, ?string $rangeEnd)
    {
        return Expense::query()
            ->with('user')
            ->when($rangeStart && $rangeEnd, fn ($q) => $q->betweenDates($rangeStart, $rangeEnd))
            ->when($search, function ($q) use ($search) {
                $term = '%'.$search.'%';
                $q->where(function ($q) use ($term) {
                    $q->where('concept', 'like', $term)
                        ->orWhere('notes', 'like', $term);
                });
            });
    }

    protected function stats($query): array
    {
        return [
            'filtered' => (float) (clone $query)->sum('amount_usd'),
            'filteredCount' => (clone $query)->count(),
            'today' => (float) Expense::query()->whereDate('expense_date', now()->toDateString())->sum('amount_usd'),
            'month' => (float) Expense::query()->betweenDates(now()->startOfMonth(), now()->endOfMonth())->sum('amount_usd'),
        ];
    }

    public function create()
    {
        return view('expenses.create', ['expense' => new Expense(['expense_date' => now()])]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['user_id'] = $request->user()->id;

        Expense::create($data);

        return redirect()->route('expenses.index')->with('status', 'Gasto registrado.');
    }

    public function edit(Expense $expense)
    {
        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $expense->update($this->validateData($request));

        return redirect()->route('expenses.index')->with('status', 'Gasto actualizado.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('status', 'Gasto eliminado.');
    }

    /**
     * Resolve the active filters and the effective date range.
     *
     * @return array{0: string, 1: ?string, 2: string, 3: string, 4: ?string, 5: ?string}
     */
    protected function filters(Request $request): array
    {
        $period = (string) $request->input('period', 'mes');
        $search = $request->input('search');

        if ($period === 'rango') {
            $start = $this->parseDate($request->input('from'), now()->startOfMonth());
            $end = $this->parseDate($request->input('to'), now());

            if ($end->lt($start)) {
                $end = $start->copy();
            }

            $rangeStart = $start->toDateString();
            $rangeEnd = $end->toDateString();
            $from = $rangeStart;
            $to = $rangeEnd;
        } else {
            [$rangeStart, $rangeEnd] = $this->periodRange($period);
            $from = $rangeStart ?: now()->startOfMonth()->toDateString();
            $to = $rangeEnd ?: now()->toDateString();
        }

        return [$period, $search, $from, $to, $rangeStart, $rangeEnd];
    }

    protected function parseDate(?string $value, Carbon $default): Carbon
    {
        if (! $value) {
            return $default->copy();
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return $default->copy();
        }
    }

    protected function periodLabel(string $period, string $from, string $to): string
    {
        if ($period === 'rango') {
            return Carbon::parse($from)->format('d/m/Y').' — '.Carbon::parse($to)->format('d/m/Y');
        }

        return match ($period) {
            'hoy' => 'Hoy',
            'semana' => 'Esta semana',
            'mes' => 'Este mes',
            default => 'Todos',
        };
    }

    /**
     * Resolve the date range for a period filter.
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function periodRange(string $period): array
    {
        return match ($period) {
            'hoy' => [now()->toDateString(), now()->toDateString()],
            'semana' => [now()->startOfWeek()->toDateString(), now()->toDateString()],
            'mes' => [now()->startOfMonth()->toDateString(), now()->toDateString()],
            default => [null, null],
        };
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'concept' => ['required', 'string', 'max:120'],
            'amount_usd' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
