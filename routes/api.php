<?php

use Illuminate\Support\Facades\Route;

// =========================================================================
// API Store — 前台 API（🇺🇸 部署）
// =========================================================================

require __DIR__ . '/sync.php';

Route::prefix('store')->name('store.')->group(function () {
    Route::get('products', [\App\Http\Controllers\Store\ProductController::class, 'index']);
    Route::get('products/{slug}', [\App\Http\Controllers\Store\ProductController::class, 'show']);
    Route::get('categories', [\App\Http\Controllers\Store\CategoryController::class, 'index']);
    Route::post('cart/calculate', [\App\Http\Controllers\Store\CartController::class, 'calculate']);
    Route::post('orders', [\App\Http\Controllers\Store\OrderController::class, 'store']);
    Route::get('orders/{orderNo}', [\App\Http\Controllers\Store\OrderController::class, 'show']);
    Route::get('settings', [\App\Http\Controllers\Store\SettingController::class, 'index']);
});
