<?php

use Illuminate\Support\Facades\Route;
use Modules\Trash\Infrastructure\Controllers\TrashController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('trash', [TrashController::class, 'index']);
    Route::post('trash/{type}/{id}/restore', [TrashController::class, 'restore']);
    Route::delete('trash/{type}/{id}', [TrashController::class, 'forceDelete']);
});
