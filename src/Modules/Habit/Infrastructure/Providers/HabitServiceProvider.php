<?php

namespace Modules\Habit\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Habit\Domain\Contracts\HabitRepositoryInterface;
use Modules\Habit\Infrastructure\Repositories\EloquentHabitRepository;

class HabitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HabitRepositoryInterface::class, EloquentHabitRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
