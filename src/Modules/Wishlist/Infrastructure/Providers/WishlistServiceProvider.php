<?php

namespace Modules\Wishlist\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Wishlist\Domain\Contracts\WishlistRepositoryInterface;
use Modules\Wishlist\Infrastructure\Repositories\EloquentWishlistRepository;

class WishlistServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WishlistRepositoryInterface::class, EloquentWishlistRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
