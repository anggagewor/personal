<?php

use Illuminate\Support\Facades\Route;
use Modules\Calendar\Infrastructure\Controllers\CalendarEventController;
use Modules\Calendar\Infrastructure\Controllers\HolidayController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('calendar-events', CalendarEventController::class);
    Route::get('holidays', [HolidayController::class, 'index']);
});
