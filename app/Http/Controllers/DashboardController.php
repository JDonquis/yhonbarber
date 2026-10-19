<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\ExchangeRateService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, ExchangeRateService $rates)
    {
        $user = $request->user();
        $isBarber = $user->isBarber();

        $start = now()->startOfDay();
        $end = now()->endOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        [$rankingStart, $rankingEnd] = $this->rankingRange($request);

        $base = fn () => Sale::query()->completed()->when($isBarber, fn ($q) => $q->where('barber_id', $user->id));

        $todaySales = $base()->between($start, $end);

        $serviceTotal = SaleItem::query()
            ->where('item_type', SaleItem::TYPE_SERVICE)
            ->whereHas('sale', fn ($q) => $q->completed()
                ->when($isBarber, fn ($qq) => $qq->where('barber_id', $user->id))
                ->between($start, $end))
            ->sum('line_total_usd');

        $productTotal = SaleItem::query()
            ->where('item_type', SaleItem::TYPE_PRODUCT)
            ->whereHas('sale', fn ($q) => $q->completed()
                ->when($isBarber, fn ($qq) => $qq->where('barber_id', $user->id))
                ->between($start, $end))
            ->sum('line_total_usd');

        $serviceCount = SaleItem::query()
            ->where('item_type', SaleItem::TYPE_SERVICE)
            ->whereHas('sale', fn ($q) => $q->completed()
                ->when($isBarber, fn ($qq) => $qq->where('barber_id', $user->id))
                ->between($start, $end))
            ->sum('quantity');

        $productCount = SaleItem::query()
            ->where('item_type', SaleItem::TYPE_PRODUCT)
            ->whereHas('sale', fn ($q) => $q->completed()
                ->when($isBarber, fn ($qq) => $qq->where('barber_id', $user->id))
                ->between($start, $end))
            ->sum('quantity');

        $monthQuery = $base()->whereBetween('sold_at', [$monthStart, $monthEnd]);

        $recentSales = $base()->with(['barber', 'items'])
            ->latest('sold_at')
            ->take(8)
            ->get();

        $todayExpenses = (float) Expense::query()->betweenDates($start, $end)->sum('amount_usd');
        $monthExpenses = (float) Expense::query()->betweenDates($monthStart, $monthEnd)->sum('amount_usd');

        $todayShopGross = (float) (clone $todaySales)->sum('shop_amount_usd');
        $monthShopGross = (float) (clone $monthQuery)->sum('total_usd') - (float) (clone $monthQuery)->sum('barber_commission_usd');

        $barberBalances = $isBarber
            ? collect()
            : User::query()->barbers()
                ->withSum(['barberSales as pending_usd' => fn ($q) => $q->completed()->unsettled()], 'barber_commission_usd')
                ->orderByDesc('pending_usd')
                ->orderBy('name')
                ->get();

        $myPending = $isBarber
            ? (float) $base()->unsettled()->sum('barber_commission_usd')
            : 0.0;

        return view('dashboard', [
            'isBarber' => $isBarber,
            'todayTotal' => (clone $todaySales)->sum('total_usd'),
            'todayCommission' => (clone $todaySales)->sum('barber_commission_usd'),
            'todayShopAmount' => round($todayShopGross - $todayExpenses, 2),
            'todayExpenses' => $todayExpenses,
            'todayTickets' => (clone $todaySales)->count(),
            'serviceTotal' => $serviceTotal,
            'serviceCount' => (int) $serviceCount,
            'productTotal' => $productTotal,
            'productCount' => (int) $productCount,
            'monthTotal' => (clone $monthQuery)->sum('total_usd'),
            'monthCommission' => (clone $monthQuery)->sum('barber_commission_usd'),
            'monthShopAmount' => round($monthShopGross - $monthExpenses, 2),
            'monthExpenses' => $monthExpenses,
            'lowStock' => $isBarber ? collect() : Product::query()->active()->lowStock()->orderBy('name')->get(),
            'topBarbers' => $isBarber ? collect() : $this->topBarbers($rankingStart, $rankingEnd),
            'rankingStart' => $rankingStart,
            'rankingEnd' => $rankingEnd,
            'barberBalances' => $barberBalances,
            'totalOwed' => (float) $barberBalances->sum('pending_usd'),
            'myPending' => $myPending,
            'recentSales' => $recentSales,
            'currentRate' => $rates->current(),
        ]);
    }

    /**
     * Ranking data for the dashboard date range filter (no page reload).
     */
    public function ranking(Request $request)
    {
        [$start, $end] = $this->rankingRange($request);

        return response()->json([
            'html' => view('dashboard._ranking', [
                'topBarbers' => $this->topBarbers($start, $end),
            ])->render(),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'label' => $start->format('d/m/Y').' — '.$end->format('d/m/Y'),
        ]);
    }

    /**
     * Resolve the ranking date range, defaulting to the current month.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function rankingRange(Request $request): array
    {
        $start = $this->parseDate($request->input('ranking_start'), now()->startOfMonth())->startOfDay();
        $end = $this->parseDate($request->input('ranking_end'), now()->endOfMonth())->endOfDay();

        if ($end->lt($start)) {
            $end = $start->copy()->endOfDay();
        }

        return [$start, $end];
    }

    protected function topBarbers(Carbon $start, Carbon $end)
    {
        return User::query()->barbers()
            ->withCount(['barberSales as tickets' => fn ($q) => $q->completed()->whereBetween('sold_at', [$start, $end])])
            ->withSum(['barberServiceItems as cuts' => fn ($q) => $q
                ->where('item_type', SaleItem::TYPE_SERVICE)
                ->whereHas('sale', fn ($sale) => $sale->completed()->whereBetween('sold_at', [$start, $end]))
            ], 'quantity')
            ->withSum(['barberSales as commission' => fn ($q) => $q->completed()->whereBetween('sold_at', [$start, $end])], 'barber_commission_usd')
            ->orderByDesc('cuts')
            ->orderByDesc('commission')
            ->take(5)
            ->get();
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
}
