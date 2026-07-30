<?php

use Illuminate\Support\Facades\Route;
use Modules\Converter\Infrastructure\Controllers\CustomCategoryController;
use Modules\Converter\Infrastructure\Controllers\CustomUnitController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('converter/categories', CustomCategoryController::class)->except(['show']);
    Route::apiResource('converter/units', CustomUnitController::class)->except(['index', 'show']);
});
