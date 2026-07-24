<?php

use Illuminate\Support\Facades\Route;
use Modules\Tag\Infrastructure\Controllers\TagController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tags', TagController::class)->except(['show']);
    Route::post('tags/{id}/attach', [TagController::class, 'attach']);
    Route::post('tags/{id}/detach', [TagController::class, 'detach']);
});
