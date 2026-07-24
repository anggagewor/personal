<?php

use Illuminate\Support\Facades\Route;
use Modules\Activity\Infrastructure\Controllers\ActivityLogController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('activity-logs', [ActivityLogController::class, 'index']);
    Route::get('activities', [ActivityLogController::class, 'index']); // alias
});
