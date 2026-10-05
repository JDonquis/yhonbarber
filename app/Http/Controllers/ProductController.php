<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->withCount('saleItems')
            ->orderBy('name')
            ->get();

        $topItem = SaleItem::query()
            ->where('item_type', SaleItem::TYPE_PRODUCT)
            ->whereNotNull('product_id')
            ->selectRaw('product_id, SUM(quantity) as units')
            ->groupBy('product_id')
            ->orderByDesc('units')
            ->with('product')
            ->first();

        $stats = [
            'total' => $products->count(),
            'active' => $products->where('active', true)->count(),
            'units' => (int) $products->sum('stock'),
            'lowStock' => $products->filter(fn ($product) => $product->isLowStock())->count(),
            'topName' => $topItem?->product?->name,
            'topUnits' => (int) ($topItem->units ?? 0),
        ];

        return view('products.index', compact('products', 'stats'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $product = Product::create($data);

        if ($product->stock > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'type' => StockMovement::TYPE_IN,
                'quantity' => $product->stock,
                'stock_after' => $product->stock,
                'note' => 'Inventario inicial',
            ]);
        }

        return redirect()->route('products.index')->with('status', 'Producto registrado.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request);

        $product->update($data);

        return redirect()->route('products.index')->with('status', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        if ($product->saleItems()->exists()) {
            return back()->with('error', 'No puedes eliminar un producto con ventas registradas. Desactívalo para ocultarlo del catálogo.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('status', 'Producto eliminado.');
    }

    public function toggleActive(Request $request, Product $product)
    {
        $product->update(['active' => ! $product->active]);

        if ($request->wantsJson()) {
            return response()->json(['active' => $product->active]);
        }

        return back()->with('status', $product->active ? 'Producto activado.' : 'Producto desactivado.');
    }

    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['nullable', 'string', 'max:60', 'unique:products,sku'.($request->route('product') ? ','.$request->route('product')->id : '')],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->boolean('active');
        $data['cost'] = $data['cost'] ?? 0;
        $data['min_stock'] = $data['min_stock'] ?? 0;

        return $data;
    }
}
