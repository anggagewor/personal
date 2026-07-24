<?php

use Illuminate\Support\Facades\Route;
use Modules\Habit\Infrastructure\Controllers\HabitController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('habits', HabitController::class)->except(['show']);
    Route::post('habits/{id}/toggle', [HabitController::class, 'toggle']);
});
