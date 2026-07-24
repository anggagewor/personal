<?php

use Illuminate\Support\Facades\Route;
use Modules\Goal\Infrastructure\Controllers\GoalController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('goals', GoalController::class)->except(['show']);
    Route::post('goals/{id}/milestones', [GoalController::class, 'addMilestone']);
    Route::post('goals/{id}/milestones/{milestoneId}/toggle', [GoalController::class, 'toggleMilestone']);
});
