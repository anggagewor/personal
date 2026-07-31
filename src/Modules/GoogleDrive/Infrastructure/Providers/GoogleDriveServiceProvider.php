<?php

namespace Modules\GoogleDrive\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\GoogleDrive\Domain\Contracts\DriveConnectionRepositoryInterface;
use Modules\GoogleDrive\Domain\Contracts\DriveServiceInterface;
use Modules\GoogleDrive\Infrastructure\Repositories\EloquentDriveConnectionRepository;
use Modules\GoogleDrive\Infrastructure\Services\GoogleDriveService;

class GoogleDriveServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DriveConnectionRepositoryInterface::class, EloquentDriveConnectionRepository::class);
        $this->app->bind(DriveServiceInterface::class, GoogleDriveService::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
