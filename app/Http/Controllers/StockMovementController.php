<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    public function index(Product $product)
    {
        $movements = $product->movements()->with('user')->latest()->paginate(20);

        return view('stock.index', compact('product', 'movements'));
    }

    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'type' => ['required', 'in:entrada,salida,ajuste'],
            'quantity' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $quantity = (int) $data['quantity'];

        $delta = match ($data['type']) {
            StockMovement::TYPE_IN => $quantity,
            StockMovement::TYPE_OUT => -$quantity,
            default => $quantity - $product->stock,
        };

        $newStock = $product->stock + $delta;

        if ($newStock < 0) {
            throw ValidationException::withMessages([
                'quantity' => 'El movimiento deja el inventario en negativo.',
            ]);
        }

        $product->update(['stock' => $newStock]);

        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'type' => $data['type'],
            'quantity' => $delta,
            'stock_after' => $newStock,
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route('products.stock.index', $product)->with('status', 'Movimiento registrado.');
    }
}
