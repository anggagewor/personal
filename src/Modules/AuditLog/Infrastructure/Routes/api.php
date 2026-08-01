<?php

use Illuminate\Support\Facades\Route;
use Modules\AuditLog\Infrastructure\Controllers\AuditLogController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/audit-logs/{type}/{id}', [AuditLogController::class, 'auditable']);
});
