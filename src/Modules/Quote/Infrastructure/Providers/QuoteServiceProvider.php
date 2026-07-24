<?php

namespace Modules\Quote\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Quote\Domain\Contracts\QuoteRepositoryInterface;
use Modules\Quote\Infrastructure\Repositories\EloquentQuoteRepository;

class QuoteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(QuoteRepositoryInterface::class, EloquentQuoteRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
