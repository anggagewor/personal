<?php

namespace Modules\ModuleManager\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\ModuleManager\Domain\Contracts\ModuleRegistryInterface;
use Modules\ModuleManager\Infrastructure\Commands\ExtractModuleCommand;
use Modules\ModuleManager\Infrastructure\Commands\ImportModuleCommand;
use Modules\ModuleManager\Infrastructure\Commands\ListModulesCommand;
use Modules\ModuleManager\Infrastructure\Repositories\FilesystemModuleRegistry;

class ModuleManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ModuleRegistryInterface::class, FilesystemModuleRegistry::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ListModulesCommand::class,
                ExtractModuleCommand::class,
                ImportModuleCommand::class,
            ]);
        }
    }
}
