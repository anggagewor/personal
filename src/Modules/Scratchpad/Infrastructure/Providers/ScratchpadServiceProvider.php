<?php

namespace Modules\Scratchpad\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Scratchpad\Domain\Contracts\ScratchpadRepositoryInterface;
use Modules\Scratchpad\Infrastructure\Repositories\EloquentScratchpadRepository;

class ScratchpadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ScratchpadRepositoryInterface::class, EloquentScratchpadRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
