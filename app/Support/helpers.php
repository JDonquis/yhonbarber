<?php

use App\Services\ExchangeRateService;
use App\Services\SettingService;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->get($key, $default);
    }
}

if (! function_exists('exchange_rate')) {
    function exchange_rate(): float
    {
        return app(ExchangeRateService::class)->current();
    }
}

if (! function_exists('usd')) {
    /**
     * Format an amount in US dollars. Example: $ 1.234,56
     */
    function usd(float|int|string|null $amount, bool $symbol = true): string
    {
        $formatted = number_format((float) $amount, 2, ',', '.');

        return $symbol ? '$ '.$formatted : $formatted;
    }
}

if (! function_exists('ves')) {
    /**
     * Format an amount in bolivars. Example: Bs. 1.234,56
     */
    function ves(float|int|string|null $amount, bool $symbol = true): string
    {
        $formatted = number_format((float) $amount, 2, ',', '.');

        return $symbol ? 'Bs. '.$formatted : $formatted;
    }
}

if (! function_exists('to_ves')) {
    /**
     * Convert a base (USD) amount to bolivars using the given rate.
     */
    function to_ves(float|int|string|null $amountUsd, ?float $rate = null): float
    {
        $rate ??= exchange_rate();

        return round((float) $amountUsd * $rate, 2);
    }
}

if (! function_exists('payment_methods')) {
    /**
     * Available payment methods configured in settings.
     *
     * @return array<int, string>
     */
    function payment_methods(): array
    {
        $raw = setting('payment_methods', 'Efectivo Bs, Efectivo USD, Pago móvil, Punto de venta, Transferencia, Zelle');

        return array_values(array_filter(array_map('trim', explode(',', (string) $raw))));
    }
}
