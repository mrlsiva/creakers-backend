<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FrontendDocsController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ClientLogoController;
use App\Http\Controllers\Api\HomeBannerController;
use App\Http\Controllers\Api\OrderStepController;
use App\Http\Controllers\Api\PriceListPdfController;
use App\Http\Controllers\Api\SafetyTipController;
use App\Http\Controllers\Api\SiteContactController;
use App\Http\Controllers\Api\SiteContentController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('frontend', [FrontendDocsController::class, 'index']);
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

    Route::get('content', [SiteContentController::class, 'index']);
    Route::get('content/{key}', [SiteContentController::class, 'show']);

    Route::get('home-banner', [HomeBannerController::class, 'show']);
    Route::get('contact', [SiteContactController::class, 'show']);
    Route::get('client-logos', [ClientLogoController::class, 'index']);
    Route::get('order-steps', [OrderStepController::class, 'index']);
    Route::get('safety-tips', [SafetyTipController::class, 'index']);
    Route::get('price-lists', [PriceListPdfController::class, 'index']);
});
