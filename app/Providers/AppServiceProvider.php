<?php

namespace App\Providers;

use App\Services\ExchangeRateService;
use App\Services\SettingService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingService::class);
        $this->app->singleton(ExchangeRateService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('shopName', setting('shop_name', config('app.name')));
            $view->with('currentRate', exchange_rate());
        });
    }
}
