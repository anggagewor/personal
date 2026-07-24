<?php

use Illuminate\Support\Facades\Route;
use Modules\Pomodoro\Infrastructure\Controllers\PomodoroController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('pomodoros', [PomodoroController::class, 'index']);
    Route::post('pomodoros', [PomodoroController::class, 'store']);
    Route::post('pomodoros/{id}/complete', [PomodoroController::class, 'complete']);
    Route::post('pomodoros/{id}/cancel', [PomodoroController::class, 'cancel']);
    Route::get('pomodoros/stats', [PomodoroController::class, 'stats']);
});
