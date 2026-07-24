<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Infrastructure\Controllers\FinanceController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('finances', FinanceController::class)->except(['show']);
    Route::get('finances/summary', [FinanceController::class, 'summary']);
});
