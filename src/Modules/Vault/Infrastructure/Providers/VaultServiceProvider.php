<?php

namespace Modules\Vault\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Vault\Domain\Contracts\VaultRepositoryInterface;
use Modules\Vault\Infrastructure\Repositories\EloquentVaultRepository;

class VaultServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(VaultRepositoryInterface::class, EloquentVaultRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
