<?php

use Illuminate\Support\Facades\Route;
use Modules\Activity\Infrastructure\Controllers\ActivityLogController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('activities', [ActivityLogController::class, 'index']);
});
