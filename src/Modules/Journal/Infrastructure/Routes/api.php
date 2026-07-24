<?php

use Illuminate\Support\Facades\Route;
use Modules\Journal\Infrastructure\Controllers\JournalController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('journals', [JournalController::class, 'index']);
    Route::post('journals', [JournalController::class, 'store']);
    Route::get('journals/moods', [JournalController::class, 'moods']);
    Route::get('journals/{date}', [JournalController::class, 'show']);
    Route::delete('journals/{id}', [JournalController::class, 'destroy']);
});
