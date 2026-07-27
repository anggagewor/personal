<?php

namespace Modules\Market\Infrastructure\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Market\Domain\Contracts\PriceHistoryRepositoryInterface;
use Modules\Market\Domain\Contracts\WatchlistRepositoryInterface;
use Modules\Market\Infrastructure\Commands\FetchMarketPricesCommand;
use Modules\Market\Infrastructure\Repositories\EloquentPriceHistoryRepository;
use Modules\Market\Infrastructure\Repositories\EloquentWatchlistRepository;

class MarketServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WatchlistRepositoryInterface::class, EloquentWatchlistRepository::class);
        $this->app->bind(PriceHistoryRepositoryInterface::class, EloquentPriceHistoryRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                FetchMarketPricesCommand::class,
            ]);
        }

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $interval = (int) config('services.twelvedata.refresh_interval', 15);

            $schedule->command('market:fetch-prices')
                ->everyFifteenMinutes()
                ->when(fn () => $interval <= 15)
                ->withoutOverlapping()
                ->runInBackground();

            $schedule->command('market:fetch-prices')
                ->cron("*/{$interval} * * * *")
                ->when(fn () => $interval > 15)
                ->withoutOverlapping()
                ->runInBackground();
        });
    }
}
