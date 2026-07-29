<?php

use Illuminate\Support\Facades\Route;
use Modules\GoogleDrive\Infrastructure\Controllers\DriveAuthController;
use Modules\GoogleDrive\Infrastructure\Controllers\DriveFileController;

Route::middleware('auth:sanctum')->prefix('drive')->group(function () {
    // Auth / Connection
    Route::get('status', [DriveAuthController::class, 'status']);
    Route::get('auth-url', [DriveAuthController::class, 'authUrl']);
    Route::post('callback', [DriveAuthController::class, 'callback']);
    Route::delete('disconnect', [DriveAuthController::class, 'disconnect']);

    // File operations
    Route::get('files', [DriveFileController::class, 'index']);
    Route::post('files/upload', [DriveFileController::class, 'upload']);
    Route::get('files/{fileId}/download', [DriveFileController::class, 'download']);
    Route::delete('files/{fileId}', [DriveFileController::class, 'destroy']);
    Route::post('folders', [DriveFileController::class, 'createFolder']);

    // Backup & Sync
    Route::post('backup', [DriveFileController::class, 'backup']);
    Route::post('sync-notes', [DriveFileController::class, 'syncNotes']);
});
