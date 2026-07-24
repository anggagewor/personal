<?php

use Illuminate\Support\Facades\Route;
use Modules\Bookmark\Infrastructure\Controllers\BookmarkController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('bookmarks', BookmarkController::class)->except(['show']);
});
