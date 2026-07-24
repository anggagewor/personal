<?php

namespace Modules\Finance\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Finance\Domain\Contracts\FinanceRepositoryInterface;
use Modules\Finance\Infrastructure\Repositories\EloquentFinanceRepository;

class FinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FinanceRepositoryInterface::class, EloquentFinanceRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
