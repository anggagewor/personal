<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Infrastructure\Controllers\AuthController;
use Modules\User\Infrastructure\Controllers\PreferenceController;
use Modules\User\Infrastructure\Controllers\ProfileController;

// Auth routes (rate limited)
Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Preferences
    Route::get('/preferences', [PreferenceController::class, 'index']);
    Route::put('/preferences', [PreferenceController::class, 'update']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar']);
});
