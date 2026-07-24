<?php

namespace Modules\Journal\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Journal\Domain\Contracts\JournalRepositoryInterface;
use Modules\Journal\Infrastructure\Repositories\EloquentJournalRepository;

class JournalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(JournalRepositoryInterface::class, EloquentJournalRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
