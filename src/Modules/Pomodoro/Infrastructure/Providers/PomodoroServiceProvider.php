<?php

namespace Modules\Pomodoro\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Pomodoro\Domain\Contracts\PomodoroRepositoryInterface;
use Modules\Pomodoro\Infrastructure\Repositories\EloquentPomodoroRepository;

class PomodoroServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PomodoroRepositoryInterface::class, EloquentPomodoroRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
