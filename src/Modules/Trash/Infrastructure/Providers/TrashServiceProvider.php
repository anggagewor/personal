<?php

namespace Modules\Trash\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Trash\Domain\Contracts\TrashRepositoryInterface;
use Modules\Trash\Infrastructure\Repositories\EloquentTrashRepository;

class TrashServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TrashRepositoryInterface::class, EloquentTrashRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
    }
}
