<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settings) {}

    public function edit()
    {
        $baseCommission = (float) $this->settings->get('commission_rate', 0);
        $barbers = User::query()->barbers()->orderBy('name')->get();

        return view('settings.edit', [
            'values' => $this->settings->all(),
            'baseCommission' => $baseCommission,
            'barbersData' => $barbers->map(fn (User $barber) => [
                'id' => $barber->id,
                'name' => $barber->name,
                'active' => (bool) $barber->active,
                'rate' => $barber->effectiveCommissionRate($baseCommission),
                'custom' => $barber->commission_rate !== null && (float) $barber->commission_rate !== $baseCommission,
            ])->values(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'shop_name' => ['required', 'string', 'max:120'],
            'shop_rif' => ['nullable', 'string', 'max:30'],
            'shop_phone' => ['nullable', 'string', 'max:30'],
            'shop_address' => ['nullable', 'string', 'max:255'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'payment_methods' => ['nullable', 'string', 'max:255'],
            'dolar_api_source' => ['required', 'in:oficial,paralelo'],
            'exchange_rate_manual' => ['nullable', 'numeric', 'min:0'],
            'exchange_rate_fallback' => ['nullable', 'numeric', 'min:0'],
            'barber_id' => ['nullable', 'integer', 'exists:users,id'],
            'barber_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'barber_use_base' => ['nullable', 'boolean'],
        ]);

        $this->settings->setMany([
            'shop_name' => $data['shop_name'],
            'shop_rif' => $data['shop_rif'] ?? '',
            'shop_phone' => $data['shop_phone'] ?? '',
            'shop_address' => $data['shop_address'] ?? '',
            'commission_rate' => $data['commission_rate'],
            'payment_methods' => $data['payment_methods'] ?? '',
            'dolar_api_source' => $data['dolar_api_source'],
            'exchange_rate_manual' => $data['exchange_rate_manual'] ?? '',
            'exchange_rate_fallback' => $data['exchange_rate_fallback'] ?? '',
        ]);

        if (! empty($data['barber_id'])) {
            $barber = User::query()->barbers()->find($data['barber_id']);

            if ($barber) {
                $rate = $request->boolean('barber_use_base') || ($data['barber_commission_rate'] ?? null) === null
                    ? null
                    : $data['barber_commission_rate'];

                $barber->update(['commission_rate' => $rate]);
            }
        }

        return redirect()->route('settings.edit')->with('status', 'Configuración guardada.');
    }
}
