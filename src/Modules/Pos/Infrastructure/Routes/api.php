<?php

use Illuminate\Support\Facades\Route;
use Modules\Pos\Infrastructure\Controllers\CategoryController;
use Modules\Pos\Infrastructure\Controllers\DiscountController;
use Modules\Pos\Infrastructure\Controllers\MemberController;
use Modules\Pos\Infrastructure\Controllers\OpenBillController;
use Modules\Pos\Infrastructure\Controllers\OrderQueueController;
use Modules\Pos\Infrastructure\Controllers\OutletController;
use Modules\Pos\Infrastructure\Controllers\PaymentMethodController;
use Modules\Pos\Infrastructure\Controllers\ProductController;
use Modules\Pos\Infrastructure\Controllers\ReceiptController;
use Modules\Pos\Infrastructure\Controllers\ReportController;
use Modules\Pos\Infrastructure\Controllers\StockController;
use Modules\Pos\Infrastructure\Controllers\TableController;
use Modules\Pos\Infrastructure\Controllers\TransactionController;
use Modules\Pos\Infrastructure\Controllers\VoucherController;

Route::middleware('auth:sanctum')->prefix('pos')->group(function () {
    // Outlets
    Route::get('/outlets', [OutletController::class, 'index']);
    Route::post('/outlets', [OutletController::class, 'store']);
    Route::put('/outlets/{id}', [OutletController::class, 'update']);
    Route::delete('/outlets/{id}', [OutletController::class, 'destroy']);

    // Categories
    Route::get('/outlets/{outletId}/categories', [CategoryController::class, 'index']);
    Route::post('/outlets/{outletId}/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    Route::post('/categories/reorder', [CategoryController::class, 'reorder']);

    // Products
    Route::get('/outlets/{outletId}/products', [ProductController::class, 'index']);
    Route::post('/outlets/{outletId}/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::post('/products/{id}/deactivate', [ProductController::class, 'deactivate']);

    // Stock
    Route::post('/products/{productId}/stock', [StockController::class, 'store']);
    Route::get('/outlets/{outletId}/stock', [StockController::class, 'index']);

    // Transactions
    Route::get('/outlets/{outletId}/transactions', [TransactionController::class, 'index']);
    Route::post('/outlets/{outletId}/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::post('/transactions/{id}/void', [TransactionController::class, 'void']);

    // Open Bills
    Route::get('/outlets/{outletId}/open-bills', [OpenBillController::class, 'index']);
    Route::post('/open-bills/{id}/close', [OpenBillController::class, 'close']);

    // Payment Methods
    Route::get('/outlets/{outletId}/payment-methods', [PaymentMethodController::class, 'index']);
    Route::post('/outlets/{outletId}/payment-methods', [PaymentMethodController::class, 'store']);
    Route::put('/payment-methods/{id}', [PaymentMethodController::class, 'update']);
    Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy']);

    // Discounts
    Route::get('/outlets/{outletId}/discounts', [DiscountController::class, 'index']);
    Route::post('/outlets/{outletId}/discounts', [DiscountController::class, 'store']);
    Route::put('/discounts/{id}', [DiscountController::class, 'update']);
    Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);
    Route::post('/discounts/evaluate', [DiscountController::class, 'evaluate']);

    // Vouchers
    Route::get('/outlets/{outletId}/vouchers', [VoucherController::class, 'index']);
    Route::post('/outlets/{outletId}/vouchers', [VoucherController::class, 'store']);
    Route::post('/outlets/{outletId}/vouchers/batch', [VoucherController::class, 'batchStore']);
    Route::get('/vouchers/{id}', [VoucherController::class, 'show']);
    Route::post('/vouchers/validate', [VoucherController::class, 'validate']);

    // Members
    Route::get('/outlets/{outletId}/members', [MemberController::class, 'index']);
    Route::post('/outlets/{outletId}/members', [MemberController::class, 'store']);
    Route::get('/members/{id}', [MemberController::class, 'show']);
    Route::put('/members/{id}', [MemberController::class, 'update']);
    Route::delete('/members/{id}', [MemberController::class, 'destroy']);
    Route::get('/outlets/{outletId}/members/search', [MemberController::class, 'search']);

    // Tables
    Route::get('/outlets/{outletId}/tables', [TableController::class, 'index']);
    Route::post('/outlets/{outletId}/tables', [TableController::class, 'store']);
    Route::delete('/tables/{id}', [TableController::class, 'destroy']);
    Route::post('/tables/{id}/close-session', [TableController::class, 'closeSession']);

    // Order Queue
    Route::get('/outlets/{outletId}/order-queue', [OrderQueueController::class, 'index']);
    Route::post('/order-queue/{id}/accept', [OrderQueueController::class, 'accept']);

    // Reports
    Route::get('/outlets/{outletId}/reports/daily', [ReportController::class, 'daily']);
    Route::get('/outlets/{outletId}/reports/range', [ReportController::class, 'range']);
    Route::get('/outlets/{outletId}/reports/products', [ReportController::class, 'products']);
    Route::get('/outlets/{outletId}/reports/payments', [ReportController::class, 'payments']);
    Route::get('/outlets/{outletId}/reports/dashboard', [ReportController::class, 'dashboard']);
    Route::get('/outlets/{outletId}/reports/export', [ReportController::class, 'export']);

    // Receipts
    Route::get('/transactions/{id}/receipt', [ReceiptController::class, 'show']);
    Route::put('/outlets/{outletId}/receipt-template', [ReceiptController::class, 'updateTemplate']);
});
