<?php

namespace Modules\Goal\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Goal\Domain\Contracts\GoalRepositoryInterface;
use Modules\Goal\Infrastructure\Repositories\EloquentGoalRepository;

class GoalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GoalRepositoryInterface::class, EloquentGoalRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
