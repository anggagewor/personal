<?php

use Illuminate\Support\Facades\Route;
use Modules\Gold\Infrastructure\Controllers\GoldController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('gold/dashboard', [GoldController::class, 'dashboard']);
    Route::get('gold/history', [GoldController::class, 'history']);
});
