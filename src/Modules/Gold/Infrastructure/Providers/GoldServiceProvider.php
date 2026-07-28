<?php

namespace Modules\Gold\Infrastructure\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Gold\Domain\Contracts\GoldPriceFetcherInterface;
use Modules\Gold\Domain\Contracts\GoldPriceRepositoryInterface;
use Modules\Gold\Infrastructure\Commands\FetchGoldDailyCommand;
use Modules\Gold\Infrastructure\Commands\ImportGoldHistoryCommand;
use Modules\Gold\Infrastructure\Repositories\EloquentGoldPriceRepository;
use Modules\Gold\Infrastructure\Services\AntamGoldPriceFetcher;

class GoldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GoldPriceRepositoryInterface::class, EloquentGoldPriceRepository::class);
        $this->app->bind(GoldPriceFetcherInterface::class, AntamGoldPriceFetcher::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportGoldHistoryCommand::class,
                FetchGoldDailyCommand::class,
            ]);
        }

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Fetch daily at 12:00 PM WIB
            $schedule->command('gold:fetch-daily')
                ->dailyAt('12:00')
                ->withoutOverlapping()
                ->runInBackground();
        });
    }
}
