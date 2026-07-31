<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Infrastructure\Controllers\DashboardController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('dashboard/weekly-summary', [DashboardController::class, 'weeklySummary']);
});
