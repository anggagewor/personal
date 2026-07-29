<?php

namespace Modules\Budget\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Budget\Domain\Contracts\BudgetRepositoryInterface;
use Modules\Budget\Infrastructure\Repositories\EloquentBudgetRepository;

class BudgetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BudgetRepositoryInterface::class, EloquentBudgetRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
    }
}
