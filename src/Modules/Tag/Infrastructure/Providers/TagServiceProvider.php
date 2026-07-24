<?php

namespace Modules\Tag\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Tag\Domain\Contracts\TagRepositoryInterface;
use Modules\Tag\Infrastructure\Repositories\EloquentTagRepository;

class TagServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TagRepositoryInterface::class, EloquentTagRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
