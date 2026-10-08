<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\ExchangeRateService;
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
            'topBarbers' => $isBarber ? collect() : $this->topBarbers($monthStart, $monthEnd),
            'recentSales' => $recentSales,
            'currentRate' => $rates->current(),
        ]);
    }

    protected function topBarbers($monthStart, $monthEnd)
    {
        return User::query()->barbers()
            ->withCount(['barberSales as tickets' => fn ($q) => $q->completed()->whereBetween('sold_at', [$monthStart, $monthEnd])])
            ->withSum(['barberSales as commission' => fn ($q) => $q->completed()->whereBetween('sold_at', [$monthStart, $monthEnd])], 'barber_commission_usd')
            ->orderByDesc('commission')
            ->take(5)
            ->get();
    }
}
