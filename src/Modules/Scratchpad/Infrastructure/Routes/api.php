<?php

use Illuminate\Support\Facades\Route;
use Modules\Scratchpad\Infrastructure\Controllers\ScratchpadController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('scratchpads', ScratchpadController::class)->except(['show']);
});
