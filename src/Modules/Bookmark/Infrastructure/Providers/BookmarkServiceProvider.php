<?php

namespace Modules\Bookmark\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Bookmark\Domain\Contracts\BookmarkRepositoryInterface;
use Modules\Bookmark\Infrastructure\Repositories\EloquentBookmarkRepository;

class BookmarkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BookmarkRepositoryInterface::class, EloquentBookmarkRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
