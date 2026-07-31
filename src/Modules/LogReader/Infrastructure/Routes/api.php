<?php

use Illuminate\Support\Facades\Route;
use Modules\LogReader\Infrastructure\Controllers\LogReaderController;

Route::middleware('auth:sanctum')->prefix('logs')->group(function () {
    Route::get('/files', [LogReaderController::class, 'files']);
    Route::get('/entries', [LogReaderController::class, 'entries']);
});
