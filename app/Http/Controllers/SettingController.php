<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settings) {}

    public function edit()
    {
        return view('settings.edit', [
            'values' => $this->settings->all(),
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

        return redirect()->route('settings.edit')->with('status', 'Configuración guardada.');
    }
}
