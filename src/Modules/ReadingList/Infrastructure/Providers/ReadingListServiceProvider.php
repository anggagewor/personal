<?php

namespace Modules\ReadingList\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\ReadingList\Domain\Contracts\ReadingListRepositoryInterface;
use Modules\ReadingList\Infrastructure\Repositories\EloquentReadingListRepository;

class ReadingListServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReadingListRepositoryInterface::class, EloquentReadingListRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
