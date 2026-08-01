<?php

namespace Modules\AuditLog\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\AuditLog\Application\Services\AuditLogger;
use Modules\AuditLog\Domain\Contracts\AuditLogDriverInterface;
use Modules\AuditLog\Domain\Contracts\AuditLogRepositoryInterface;
use Modules\AuditLog\Infrastructure\Drivers\DatabaseAuditLogDriver;
use Modules\AuditLog\Infrastructure\Repositories\EloquentAuditLogRepository;

class AuditLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/audit-log.php', 'audit-log');

        $this->app->bind(AuditLogDriverInterface::class, function ($app) {
            $driver = config('audit-log.driver', 'database');

            return match ($driver) {
                'database' => $app->make(DatabaseAuditLogDriver::class),
                default => $app->make(DatabaseAuditLogDriver::class),
            };
        });

        $this->app->bind(AuditLogRepositoryInterface::class, EloquentAuditLogRepository::class);

        $this->app->singleton(AuditLogger::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');

        $this->publishes([
            __DIR__ . '/../Config/audit-log.php' => config_path('audit-log.php'),
        ], 'audit-log-config');
    }
}
