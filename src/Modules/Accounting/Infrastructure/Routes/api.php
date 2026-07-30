<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Infrastructure\Controllers\AccountController;
use Modules\Accounting\Infrastructure\Controllers\JournalEntryController;
use Modules\Accounting\Infrastructure\Controllers\LedgerController;
use Modules\Accounting\Infrastructure\Controllers\ReportController;
use Modules\Accounting\Infrastructure\Controllers\ResetController;

Route::middleware('auth:sanctum')->prefix('accounting')->group(function () {
    // Account CRUD (no show)
    Route::apiResource('accounts', AccountController::class)->except(['show']);

    // Journal Entry CRUD (with show)
    Route::apiResource('journal-entries', JournalEntryController::class);

    // Ledger
    Route::get('ledger/{accountId}', [LedgerController::class, 'show']);

    // Reports
    Route::get('reports/trial-balance', [ReportController::class, 'trialBalance']);
    Route::get('reports/income-statement', [ReportController::class, 'incomeStatement']);
    Route::get('reports/balance-sheet', [ReportController::class, 'balanceSheet']);

    // Reset & Sample
    Route::post('reset/journal', [ResetController::class, 'resetJournal']);
    Route::post('reset/all', [ResetController::class, 'resetAll']);
    Route::post('sample-data', [ResetController::class, 'loadSample']);
});
