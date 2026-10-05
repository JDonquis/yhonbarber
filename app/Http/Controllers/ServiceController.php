<?php

namespace App\Http\Controllers;

use App\Models\SaleItem;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::query()->orderBy('name')->get();

        $serviceItems = SaleItem::query()
            ->where('item_type', SaleItem::TYPE_SERVICE)
            ->whereNotNull('service_id');

        $totalTickets = (clone $serviceItems)->count();

        $topItem = (clone $serviceItems)
            ->selectRaw('service_id, COUNT(*) as tickets')
            ->groupBy('service_id')
            ->orderByDesc('tickets')
            ->with('service')
            ->first();

        $stats = [
            'total' => $services->count(),
            'active' => $services->where('active', true)->count(),
            'topName' => $topItem?->service?->name,
            'topPct' => ($topItem && $totalTickets > 0)
                ? (int) round($topItem->tickets / $totalTickets * 100)
                : 0,
        ];

        $commissionRate = (float) setting('commission_rate', 0);

        return view('services.index', compact('services', 'stats', 'commissionRate'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        Service::create($data);

        return redirect()->route('services.index')->with('status', 'Tipo de corte registrado.');
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validateData($request);

        $service->update($data);

        return redirect()->route('services.index')->with('status', 'Tipo de corte actualizado.');
    }

    public function destroy(Service $service)
    {
        if ($service->saleItems()->exists()) {
            return back()->with('error', 'No puedes eliminar un corte con ventas registradas. Páusalo para ocultarlo del catálogo.');
        }

        $service->delete();

        return redirect()->route('services.index')->with('status', 'Tipo de corte eliminado.');
    }

    public function toggleActive(Service $service)
    {
        $service->update(['active' => ! $service->active]);

        if (request()->wantsJson()) {
            return response()->json(['active' => $service->active]);
        }

        return back()->with('status', $service->active ? 'Tipo de corte disponible.' : 'Tipo de corte pausado.');
    }

    public function updatePrice(Request $request, Service $service)
    {
        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $service->update(['price' => $data['price']]);

        if ($request->wantsJson()) {
            return response()->json([
                'price' => (float) $service->price,
                'ves' => to_ves($service->price),
            ]);
        }

        return back()->with('status', 'Precio actualizado.');
    }

    protected function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->boolean('active');

        return $data;
    }
}
