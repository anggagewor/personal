<?php

use Illuminate\Support\Facades\Route;
use Modules\Weather\Infrastructure\Controllers\WeatherController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('weather/current', [WeatherController::class, 'current']);
});
