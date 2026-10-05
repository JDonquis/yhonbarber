<?php

use App\Http\Controllers\BarberController;
use App\Http\Controllers\ClosingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\PasswordResetRequestController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Ventas (disponible para administradores y barberos)
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/service/create', [SaleController::class, 'createService'])->name('sales.create-service');
    Route::post('/sales/service', [SaleController::class, 'storeService'])->name('sales.store-service');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Administración
    Route::middleware('role:admin')->group(function () {
        Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');

        // Venta de productos: solo administradores
        Route::get('/sales/product/create', [SaleController::class, 'createProduct'])->name('sales.create-product');
        Route::post('/sales/product', [SaleController::class, 'storeProduct'])->name('sales.store-product');

        Route::resource('services', ServiceController::class)->except(['show']);
        Route::post('/services/{service}/toggle', [ServiceController::class, 'toggleActive'])->name('services.toggle');
        Route::post('/services/{service}/price', [ServiceController::class, 'updatePrice'])->name('services.update-price');
        Route::resource('products', ProductController::class)->except(['show']);
        Route::post('/products/{product}/toggle', [ProductController::class, 'toggleActive'])->name('products.toggle');
        Route::resource('barbers', BarberController::class)->except(['show']);
        Route::post('/barbers/{barber}/toggle', [BarberController::class, 'toggleActive'])->name('barbers.toggle');

        Route::resource('users', UserController::class)->except(['show']);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        Route::get('/password-requests', [PasswordResetRequestController::class, 'index'])->name('password-requests.index');
        Route::post('/password-requests/{passwordResetRequest}/resolve', [PasswordResetRequestController::class, 'resolve'])->name('password-requests.resolve');
        Route::delete('/password-requests/{passwordResetRequest}', [PasswordResetRequestController::class, 'destroy'])->name('password-requests.destroy');

        Route::get('/products/{product}/stock', [StockMovementController::class, 'index'])->name('products.stock.index');
        Route::post('/products/{product}/stock', [StockMovementController::class, 'store'])->name('products.stock.store');

        Route::get('/closings', [ClosingController::class, 'index'])->name('closings.index');
        Route::post('/closings', [ClosingController::class, 'generate'])->name('closings.generate');
        Route::get('/closings/{closing}', [ClosingController::class, 'show'])->name('closings.show');
        Route::post('/closings/{closing}/close', [ClosingController::class, 'close'])->name('closings.close');
        Route::post('/closings/{closing}/reopen', [ClosingController::class, 'reopen'])->name('closings.reopen');
        Route::get('/closings/{closing}/print', [ClosingController::class, 'print'])->name('closings.print');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/exchange-rate/refresh', [ExchangeRateController::class, 'refresh'])->name('exchange-rate.refresh');
        Route::post('/exchange-rate/clear-manual', [ExchangeRateController::class, 'clearManual'])->name('exchange-rate.clear-manual');
    });
});

require __DIR__.'/auth.php';
