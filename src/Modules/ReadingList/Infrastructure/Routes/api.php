<?php

use Illuminate\Support\Facades\Route;
use Modules\ReadingList\Infrastructure\Controllers\ReadingListController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('reading-list', [ReadingListController::class, 'index']);
    Route::post('reading-list', [ReadingListController::class, 'store']);
    Route::delete('reading-list/{id}', [ReadingListController::class, 'destroy']);
    Route::post('reading-list/{id}/toggle-read', [ReadingListController::class, 'toggleRead']);
    Route::post('reading-list/{id}/toggle-favorite', [ReadingListController::class, 'toggleFavorite']);
});
