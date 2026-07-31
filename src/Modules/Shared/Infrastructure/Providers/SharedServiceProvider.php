<?php

namespace Modules\Shared\Infrastructure\Providers;

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
