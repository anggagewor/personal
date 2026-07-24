<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes are now loaded per-module via ServiceProviders.
| See: src/Modules/{Module}/Infrastructure/Routes/api.php
|
| This file is kept for any global/cross-cutting API routes.
|
*/

// Public routes (no auth needed)
Route::get('/public/holidays', [\Modules\Calendar\Infrastructure\Controllers\HolidayController::class, 'index']);
