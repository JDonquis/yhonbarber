<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    protected const CACHE_KEY = 'exchange_rate.current';

    protected const SOURCES = [
        'oficial' => 'https://ve.dolarapi.com/v1/dolares/oficial',
        'paralelo' => 'https://ve.dolarapi.com/v1/dolares/paralelo',
    ];

    public function __construct(protected SettingService $settings) {}

    /**
     * Current USD to VES rate in bolivars.
     */
    public function current(): float
    {
        $manual = $this->settings->get('exchange_rate_manual');

        if ($manual !== null && (float) $manual > 0) {
            return (float) $manual;
        }

        $rate = Cache::remember(self::CACHE_KEY, now()->addHour(), function () {
            return $this->fetch();
        });

        return (float) $rate;
    }

    /**
     * Force a fresh value from the API and cache it.
     */
    public function refresh(): ?float
    {
        Cache::forget(self::CACHE_KEY);

        $rate = $this->fetch();

        if ($rate !== null) {
            Cache::put(self::CACHE_KEY, $rate, now()->addHour());
        }

        return $rate;
    }

    public function usingManual(): bool
    {
        $manual = $this->settings->get('exchange_rate_manual');

        return $manual !== null && (float) $manual > 0;
    }

    /**
     * Last stored rate, used as a fallback when the API fails.
     */
    public function lastKnown(): float
    {
        $last = ExchangeRate::query()->latest('fetched_at')->first();

        if ($last) {
            return (float) $last->rate;
        }

        return (float) $this->settings->get('exchange_rate_fallback', 0);
    }

    public function source(): string
    {
        return $this->settings->get('dolar_api_source') ?: config('app.dolar_api_source', 'oficial');
    }

    protected function fetch(): ?float
    {
        $source = $this->source();
        $url = self::SOURCES[$source] ?? self::SOURCES['oficial'];

        try {
            $response = Http::timeout(10)->retry(2, 500)->get($url);

            if (! $response->successful()) {
                return $this->lastKnown() ?: null;
            }

            $rate = (float) ($response->json('promedio') ?? 0);

            if ($rate <= 0) {
                return $this->lastKnown() ?: null;
            }

            ExchangeRate::create([
                'source' => $source,
                'rate' => $rate,
                'fetched_at' => now(),
            ]);

            return $rate;
        } catch (\Throwable $e) {
            Log::warning('No se pudo obtener la tasa del dólar: '.$e->getMessage());

            return $this->lastKnown() ?: null;
        }
    }
}
