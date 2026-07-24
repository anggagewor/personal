<?php

use Illuminate\Support\Facades\Route;
use Modules\Shared\Infrastructure\Controllers\DashboardController;
use Modules\Shared\Infrastructure\Controllers\WeatherController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('weather/current', [WeatherController::class, 'current']);
    Route::get('dashboard/weekly-summary', [DashboardController::class, 'weeklySummary']);
});
