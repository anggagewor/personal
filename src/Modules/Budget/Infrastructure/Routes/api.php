<?php

use Illuminate\Support\Facades\Route;
use Modules\Budget\Infrastructure\Controllers\BudgetController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('budgets', BudgetController::class)->except(['show']);
    Route::get('budgets/summary', [BudgetController::class, 'summary']);
});
