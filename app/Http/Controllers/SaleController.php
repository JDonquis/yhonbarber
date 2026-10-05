<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Service;
use App\Models\User;
use App\Services\SaleService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use RuntimeException;

class SaleController extends Controller
{
    public function __construct(protected SaleService $sales) {}

    public function index(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $period = $request->input('period');

        if ($period === null && ! $from && ! $to) {
            $period = 'hoy';
        }

        if ($period && $period !== 'all') {
            [$from, $to] = match ($period) {
                'hoy' => [now()->toDateString(), now()->toDateString()],
                'ayer' => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
                'semana' => [now()->startOfWeek()->toDateString(), now()->toDateString()],
                'mes' => [now()->startOfMonth()->toDateString(), now()->toDateString()],
                default => [null, null],
            };
        } elseif ($period === 'all') {
            $from = $to = null;
        }

        $filters = function ($query) use ($request, $from, $to) {
            if ($request->user()->isBarber()) {
                $query->where('barber_id', $request->user()->id);
            } elseif ($request->filled('barber_id')) {
                $query->where('barber_id', $request->input('barber_id'));
            }

            if ($from) {
                $query->whereDate('sold_at', '>=', $from);
            }

            if ($to) {
                $query->whereDate('sold_at', '<=', $to);
            }

            if ($request->filled('search')) {
                $term = '%'.$request->input('search').'%';
                $query->where(function ($query) use ($term) {
                    $query->where('code', 'like', $term)
                        ->orWhere('notes', 'like', $term)
                        ->orWhereHas('barber', fn ($barber) => $barber->where('name', 'like', $term))
                        ->orWhereHas('items', fn ($item) => $item->where('name', 'like', $term));
                });
            }
        };

        $sales = Sale::query()->with(['barber', 'user', 'items'])
            ->tap($filters)
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->latest('sold_at')
            ->paginate(15)
            ->withQueryString();

        $summary = Sale::query()->completed()
            ->tap($filters)
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(total_usd), 0) as total_usd')
            ->selectRaw('COALESCE(SUM(total_ves), 0) as total_ves')
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN total_usd ELSE 0 END), 0) as service_usd', [Sale::TYPE_SERVICE])
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN total_ves ELSE 0 END), 0) as service_ves', [Sale::TYPE_SERVICE])
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN 1 ELSE 0 END), 0) as service_count', [Sale::TYPE_SERVICE])
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN total_usd ELSE 0 END), 0) as product_usd', [Sale::TYPE_PRODUCT])
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN total_ves ELSE 0 END), 0) as product_ves', [Sale::TYPE_PRODUCT])
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN 1 ELSE 0 END), 0) as product_count', [Sale::TYPE_PRODUCT])
            ->first();

        $barbers = User::query()->barbers()->orderBy('name')->get();

        return view('sales.index', compact('sales', 'barbers', 'summary', 'from', 'to', 'period'));
    }

    public function createService()
    {
        return view('sales.create-service', [
            'services' => Service::query()->active()->orderBy('name')->get(),
            'barbers' => User::query()->active()->barbers()->orderBy('name')->get(),
            'paymentMethods' => payment_methods(),
            'commissionRate' => (float) app(SettingService::class)->get('commission_rate', 0),
        ]);
    }

    public function storeService(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'barber_id' => ['nullable', 'exists:users,id'],
            'price' => ['nullable', 'numeric', 'min:0.01', 'max:99999.99'],
            'update_service_price' => ['nullable', 'boolean'],
            'payment_method' => ['nullable', 'string', 'max:60'],
            'payment_currency' => ['required', 'in:USD,VES'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->user()->isBarber()) {
            $data['barber_id'] = $request->user()->id;
        }

        $data['update_service_price'] = $request->user()->isAdmin()
            && $request->boolean('update_service_price');

        if (empty($data['barber_id'])) {
            return back()->withInput()->with('error', 'Debes seleccionar el barbero que realizó el corte.');
        }

        try {
            $sale = $this->sales->registerServiceSale($data, $request->user());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sales.show', $sale)->with('status', 'Corte registrado correctamente.');
    }

    public function createProduct()
    {
        return view('sales.create-product', [
            'products' => Product::query()->active()->orderBy('name')->get(),
            'paymentMethods' => payment_methods(),
        ]);
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['nullable', 'string', 'max:60'],
            'payment_currency' => ['required', 'in:USD,VES'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $sale = $this->sales->registerProductSale($data['items'], $data, $request->user());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sales.show', $sale)->with('status', 'Venta de productos registrada.');
    }

    public function show(Request $request, Sale $sale)
    {
        $this->authorizeView($request, $sale);

        $sale->load(['items.service', 'items.product', 'barber', 'user']);

        return view('sales.show', compact('sale'));
    }

    public function print(Request $request, Sale $sale)
    {
        $this->authorizeView($request, $sale);

        $sale->load(['items', 'barber', 'user']);

        return view('sales.print', compact('sale'));
    }

    public function cancel(Request $request, Sale $sale)
    {
        $this->sales->cancel($sale, $request->user());

        return redirect()->route('sales.show', $sale)->with('status', 'Venta anulada y stock restituido.');
    }

    protected function authorizeView(Request $request, Sale $sale): void
    {
        if ($request->user()->isBarber() && $sale->barber_id !== $request->user()->id && $sale->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
