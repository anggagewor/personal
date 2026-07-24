<?php

use Illuminate\Support\Facades\Route;
use Modules\Note\Infrastructure\Controllers\NoteController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('notes', NoteController::class);
    Route::post('notes/{id}/toggle-pin', [NoteController::class, 'togglePin']);
});
