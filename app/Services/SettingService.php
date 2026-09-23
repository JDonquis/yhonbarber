<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    protected const CACHE_KEY = 'settings.all';

    protected ?array $values = null;

    /**
     * Return every setting as an associative array.
     */
    public function all(): array
    {
        if ($this->values === null) {
            $this->values = Cache::rememberForever(self::CACHE_KEY, function () {
                return Setting::query()->pluck('value', 'key')->all();
            });
        }

        return $this->values;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $values = $this->all();

        return $values[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        $this->forget();
    }

    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->forget();
    }

    public function forget(): void
    {
        $this->values = null;

        Cache::forget(self::CACHE_KEY);
    }
}
