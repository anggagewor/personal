<?php

use Illuminate\Support\Facades\Route;
use Modules\Quote\Infrastructure\Controllers\QuoteController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('quotes', [QuoteController::class, 'index']);
    Route::get('quotes/today', [QuoteController::class, 'today']);
    Route::post('quotes', [QuoteController::class, 'store']);
    Route::delete('quotes/{id}', [QuoteController::class, 'destroy']);
});
