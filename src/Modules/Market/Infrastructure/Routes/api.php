<?php

use Illuminate\Support\Facades\Route;
use Modules\Market\Infrastructure\Controllers\MarketController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('market/watchlist', [MarketController::class, 'index']);
    Route::post('market/watchlist', [MarketController::class, 'store']);
    Route::delete('market/watchlist/{id}', [MarketController::class, 'destroy']);
    Route::get('market/prices', [MarketController::class, 'prices']);
    Route::get('market/dashboard', [MarketController::class, 'dashboard']);
    Route::get('market/history/{symbol}', [MarketController::class, 'history'])->where('symbol', '.*');
    Route::get('market/ohlc/{symbol}', [MarketController::class, 'ohlc'])->where('symbol', '.*');
    Route::get('market/config', [MarketController::class, 'config']);
    Route::get('market/export', [MarketController::class, 'export']);
    Route::post('market/import', [MarketController::class, 'import']);
    Route::get('market/import/template', [MarketController::class, 'template']);
});
