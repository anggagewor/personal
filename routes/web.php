<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SPA Catch-All
|--------------------------------------------------------------------------
|
| Serve the Vue SPA for all non-API routes.
| Vue Router handles client-side navigation.
|
*/

Route::get('/{any?}', fn () => view('app'))
    ->where('any', '^(?!api).*$')
    ->name('spa');

