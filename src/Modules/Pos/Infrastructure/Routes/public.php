<?php

use Illuminate\Support\Facades\Route;
use Modules\Pos\Infrastructure\Controllers\QrOrderPublicController;

Route::prefix('pos/qr')->group(function () {
    Route::get('/{token}/menu', [QrOrderPublicController::class, 'menu']);
    Route::post('/{token}/order', [QrOrderPublicController::class, 'submitOrder']);
    Route::get('/{token}/order/{id}', [QrOrderPublicController::class, 'orderStatus']);
});
