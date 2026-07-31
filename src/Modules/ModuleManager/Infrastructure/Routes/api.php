<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleManager\Infrastructure\Controllers\ModuleManagerController;

Route::middleware('auth:sanctum')->prefix('modules')->group(function () {
    Route::get('/', [ModuleManagerController::class, 'index']);
    Route::get('/graph', [ModuleManagerController::class, 'graph']);
    Route::get('/health', [ModuleManagerController::class, 'health']);
    Route::get('/{name}', [ModuleManagerController::class, 'show']);
    Route::get('/{name}/inspect', [ModuleManagerController::class, 'inspect']);
    Route::get('/{name}/impact', [ModuleManagerController::class, 'impact']);
    Route::get('/{name}/extract-preview', [ModuleManagerController::class, 'extractPreview']);
    Route::post('/{name}/extract', [ModuleManagerController::class, 'extract']);
    Route::post('/import', [ModuleManagerController::class, 'import']);
});
