<?php

namespace App\Http\Controllers;

use App\Services\ExchangeRateService;
use App\Services\SettingService;

class ExchangeRateController extends Controller
{
    public function __construct(
        protected ExchangeRateService $rates,
        protected SettingService $settings,
    ) {}

    public function refresh()
    {
        $rate = $this->rates->refresh();

        if ($rate === null) {
            return back()->with('error', 'No se pudo obtener la tasa del dólar. Se mantiene la última conocida.');
        }

        return back()->with('status', 'Tasa actualizada: Bs. '.number_format($rate, 2, ',', '.').' por $1.');
    }

    public function clearManual()
    {
        $this->settings->set('exchange_rate_manual', '');
        $this->rates->refresh();

        return redirect()->route('settings.edit')->with('status', 'Tasa manual eliminada. Se usará la API automáticamente.');
    }
}
