<?php

use Illuminate\Support\Facades\Route;

// =========================================================================
// API Store — 前台 API（🇺🇸 部署）
// =========================================================================

Route::prefix('store')->name('store.')->group(function () {
    Route::get('products', [\App\Http\Controllers\Store\ProductController::class, 'index']);
    Route::get('products/{slug}', [\App\Http\Controllers\Store\ProductController::class, 'show']);
    Route::get('categories', [\App\Http\Controllers\Store\CategoryController::class, 'index']);
    Route::get('colors', [\App\Http\Controllers\Store\ColorController::class, 'index']);
    Route::get('size-guide', [\App\Http\Controllers\Store\SizeGuideController::class, 'show']);
    Route::get('cart', [\App\Http\Controllers\Store\CartController::class, 'show']);
    Route::post('cart/sync', [\App\Http\Controllers\Store\CartController::class, 'sync']);
    Route::post('cart/calculate', [\App\Http\Controllers\Store\CartController::class, 'calculate']);
    Route::post('orders', [\App\Http\Controllers\Store\OrderController::class, 'store']);
    Route::get('orders/{orderNo}', [\App\Http\Controllers\Store\OrderController::class, 'show']);
    Route::post('contact', [\App\Http\Controllers\Store\ContactController::class, 'store'])
        ->middleware('throttle:10,1');
    Route::get('settings', [\App\Http\Controllers\Store\SettingController::class, 'index']);
});
