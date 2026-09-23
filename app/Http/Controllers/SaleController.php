<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Service;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Http\Request;
use RuntimeException;

class SaleController extends Controller
{
    public function __construct(protected SaleService $sales) {}

    public function index(Request $request)
    {
        $query = Sale::query()->with(['barber', 'user', 'items'])->latest('sold_at');

        if ($request->user()->isBarber()) {
            $query->where('barber_id', $request->user()->id);
        } elseif ($request->filled('barber_id')) {
            $query->where('barber_id', $request->input('barber_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('from')) {
            $query->whereDate('sold_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('sold_at', '<=', $request->input('to'));
        }

        $sales = $query->paginate(20)->withQueryString();
        $barbers = User::query()->barbers()->orderBy('name')->get();

        return view('sales.index', compact('sales', 'barbers'));
    }

    public function createService()
    {
        return view('sales.create-service', [
            'services' => Service::query()->active()->orderBy('name')->get(),
            'barbers' => User::query()->active()->barbers()->orderBy('name')->get(),
            'paymentMethods' => payment_methods(),
        ]);
    }

    public function storeService(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'barber_id' => ['nullable', 'exists:users,id'],
            'payment_method' => ['nullable', 'string', 'max:60'],
            'payment_currency' => ['required', 'in:USD,VES'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->user()->isBarber()) {
            $data['barber_id'] = $request->user()->id;
        }

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
