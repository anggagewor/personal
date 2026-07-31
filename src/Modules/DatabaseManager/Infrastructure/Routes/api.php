<?php

use Illuminate\Support\Facades\Route;
use Modules\DatabaseManager\Infrastructure\Controllers\DatabaseManagerController;

Route::middleware('auth:sanctum')->prefix('database')->group(function () {
    Route::get('/tables', [DatabaseManagerController::class, 'tables']);
    Route::get('/tables/{table}/structure', [DatabaseManagerController::class, 'structure']);
    Route::get('/tables/{table}/rows', [DatabaseManagerController::class, 'rows']);
    Route::put('/tables/{table}/rows', [DatabaseManagerController::class, 'updateRow']);
    Route::delete('/tables/{table}/rows', [DatabaseManagerController::class, 'deleteRow']);
    Route::post('/tables/{table}/alter', [DatabaseManagerController::class, 'alterTable']);
    Route::post('/query', [DatabaseManagerController::class, 'query']);
});
