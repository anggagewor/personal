<?php

use Illuminate\Support\Facades\Route;
use Modules\Vault\Infrastructure\Controllers\VaultController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('vault', VaultController::class)->except(['show']);
    Route::get('vault/categories', [VaultController::class, 'categories']);
});
