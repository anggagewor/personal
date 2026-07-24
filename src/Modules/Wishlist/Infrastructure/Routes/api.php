<?php

use Illuminate\Support\Facades\Route;
use Modules\Wishlist\Infrastructure\Controllers\WishlistController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('wishlists', WishlistController::class)->except(['show']);
    Route::post('wishlists/{id}/toggle', [WishlistController::class, 'toggle']);
});
