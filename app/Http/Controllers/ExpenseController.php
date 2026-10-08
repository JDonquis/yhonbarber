<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'mes');
        $search = $request->input('search');

        [$from, $to] = $this->periodRange($period);

        $query = Expense::query()
            ->with('user')
            ->when($from && $to, fn ($q) => $q->betweenDates($from, $to))
            ->when($search, function ($q) use ($search) {
                $term = '%'.$search.'%';
                $q->where(function ($q) use ($term) {
                    $q->where('concept', 'like', $term)
                        ->orWhere('notes', 'like', $term);
                });
            });

        $expenses = (clone $query)
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'filtered' => (float) (clone $query)->sum('amount_usd'),
            'filteredCount' => (clone $query)->count(),
            'today' => (float) Expense::query()->whereDate('expense_date', now()->toDateString())->sum('amount_usd'),
            'month' => (float) Expense::query()->betweenDates(now()->startOfMonth(), now()->endOfMonth())->sum('amount_usd'),
        ];

        return view('expenses.index', compact('expenses', 'stats', 'period', 'search'));
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
