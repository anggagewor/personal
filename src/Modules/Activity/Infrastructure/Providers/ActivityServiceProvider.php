<?php

namespace Modules\Activity\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Activity\Domain\Contracts\ActivityLogRepositoryInterface;
use Modules\Activity\Infrastructure\Repositories\EloquentActivityLogRepository;

class ActivityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ActivityLogRepositoryInterface::class, EloquentActivityLogRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
