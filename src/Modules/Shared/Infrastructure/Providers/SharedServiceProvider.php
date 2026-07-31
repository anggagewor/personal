<?php

namespace Modules\Shared\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Shared\Infrastructure\Commands\Foundry\DoctorCommand;
use Modules\Shared\Infrastructure\Commands\Foundry\GraphCommand;
use Modules\Shared\Infrastructure\Commands\Foundry\ScanCommand;
use Modules\Shared\Infrastructure\Commands\Foundry\VerifyCommand;

class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ScanCommand::class,
                GraphCommand::class,
                VerifyCommand::class,
                DoctorCommand::class,
            ]);
        }
    }
}
