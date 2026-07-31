<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleManager\Infrastructure\Controllers\ModuleManagerController;

Route::middleware('auth:sanctum')->prefix('modules')->group(function () {
    Route::get('/', [ModuleManagerController::class, 'index']);
    Route::get('/{name}', [ModuleManagerController::class, 'show']);
    Route::post('/{name}/extract', [ModuleManagerController::class, 'extract']);
    Route::post('/import', [ModuleManagerController::class, 'import']);
});
