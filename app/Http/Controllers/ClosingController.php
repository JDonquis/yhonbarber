<?php

namespace App\Http\Controllers;

use App\Models\Closing;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\User;
use App\Services\ClosingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClosingController extends Controller
{
    public function __construct(protected ClosingService $closings) {}

    public function index()
    {
        $closings = Closing::query()->with(['closedBy', 'barber'])->latest('period_start')->get();
        $barbers = User::query()->barbers()->orderBy('name')->get();

        $monthStart = now()->startOfMonth();
        $monthRevenue = (float) Sale::query()->completed()->where('sold_at', '>=', $monthStart)->sum('total_usd');
        $monthCommission = (float) Sale::query()->completed()->where('sold_at', '>=', $monthStart)->sum('barber_commission_usd');
        $monthExpenses = (float) Expense::query()->betweenDates($monthStart, now()->endOfMonth())->sum('amount_usd');
        $monthShopAmount = round($monthRevenue - $monthCommission - $monthExpenses, 2);

        return view('closings.index', compact('closings', 'barbers', 'monthRevenue', 'monthCommission', 'monthExpenses', 'monthShopAmount'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'period_type' => ['required', 'in:diario,semanal,mensual'],
            'date' => ['required', 'date'],
            'barber_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $barber = null;

        if (! empty($data['barber_id'])) {
            $barber = User::query()->barbers()->findOrFail($data['barber_id']);
        }

        $closing = $this->closings->generate(
            $data['period_type'],
            Carbon::parse($data['date']),
            $request->user(),
            $barber,
        );

        return redirect()->route('closings.show', $closing)->with('status', $barber ? 'Cierre de barbero generado.' : 'Reporte generado.');
    }

    public function show(Closing $closing)
    {
        return view('closings.show', compact('closing'));
    }

    public function close(Request $request, Closing $closing)
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->closings->close($closing, $request->user(), $data['notes'] ?? null);

        return redirect()->route('closings.show', $closing)->with('status', 'Cierre bloqueado correctamente.');
    }

    public function reopen(Request $request, Closing $closing)
    {
        $this->closings->reopen($closing, $request->user());

        return redirect()->route('closings.show', $closing)->with('status', 'Período reabierto. Ya puedes registrar o corregir ventas.');
    }

    public function print(Closing $closing)
    {
        return view('closings.print', compact('closing'));
    }
}
