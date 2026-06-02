<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('sites', [SiteController::class, 'index']);

Route::prefix('{site}')->group(function () {
    Route::get('/', [SiteController::class, 'show']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{categorySlug}/products', [ProductController::class, 'byCategory']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{slug}', [ProductController::class, 'show']);

    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/track', [OrderController::class, 'track']);
    Route::get('orders/{orderNumber}', [OrderController::class, 'show']);
});
