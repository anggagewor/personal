<?php

use Illuminate\Support\Facades\Route;
use Modules\Supplier\Infrastructure\Controllers\GoodsReceiptController;
use Modules\Supplier\Infrastructure\Controllers\PurchaseOrderController;
use Modules\Supplier\Infrastructure\Controllers\SupplierController;
use Modules\Supplier\Infrastructure\Controllers\SupplierPaymentController;
use Modules\Supplier\Infrastructure\Controllers\SupplierProductController;
use Modules\Supplier\Infrastructure\Controllers\SupplierReportController;

Route::middleware('auth:sanctum')->prefix('supplier')->group(function () {
    // Supplier routes
    Route::get('/outlets/{outletId}/suppliers', [SupplierController::class, 'index']);
    Route::post('/outlets/{outletId}/suppliers', [SupplierController::class, 'store']);
    Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
    Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']);
    Route::get('/outlets/{outletId}/suppliers/search', [SupplierController::class, 'search']);

    // Purchase Order routes
    Route::get('/outlets/{outletId}/purchase-orders', [PurchaseOrderController::class, 'index']);
    Route::post('/outlets/{outletId}/purchase-orders', [PurchaseOrderController::class, 'store']);
    Route::get('/purchase-orders/{id}', [PurchaseOrderController::class, 'show']);
    Route::put('/purchase-orders/{id}', [PurchaseOrderController::class, 'update']);
    Route::post('/purchase-orders/{id}/confirm', [PurchaseOrderController::class, 'confirm']);
    Route::post('/purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel']);

    // Goods Receipt routes
    Route::get('/purchase-orders/{id}/receipts', [GoodsReceiptController::class, 'index']);
    Route::post('/purchase-orders/{id}/receipts', [GoodsReceiptController::class, 'store']);

    // Payment routes
    Route::get('/purchase-orders/{id}/payments', [SupplierPaymentController::class, 'indexByPO']);
    Route::post('/purchase-orders/{id}/payments', [SupplierPaymentController::class, 'store']);
    Route::get('/suppliers/{id}/payments', [SupplierPaymentController::class, 'indexBySupplier']);

    // Supplier-Product Link routes
    Route::get('/suppliers/{id}/products', [SupplierProductController::class, 'index']);
    Route::post('/suppliers/{id}/products', [SupplierProductController::class, 'link']);
    Route::delete('/suppliers/{id}/products/{variantId}', [SupplierProductController::class, 'unlink']);

    // Report routes
    Route::get('/outlets/{outletId}/reports/summary', [SupplierReportController::class, 'summary']);
    Route::get('/outlets/{outletId}/reports/by-supplier', [SupplierReportController::class, 'bySupplier']);
    Route::get('/outlets/{outletId}/reports/by-product', [SupplierReportController::class, 'byProduct']);
    Route::get('/outlets/{outletId}/reports/export', [SupplierReportController::class, 'export']);

    // Dashboard
    Route::get('/outlets/{outletId}/dashboard', [SupplierReportController::class, 'dashboard']);
});
