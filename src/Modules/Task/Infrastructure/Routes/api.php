<?php

use Illuminate\Support\Facades\Route;
use Modules\Task\Infrastructure\Controllers\TaskController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/reorder', [TaskController::class, 'reorder']);
});
