<?php

namespace Modules\Gold\Infrastructure\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Gold\Domain\Contracts\GoldPriceRepositoryInterface;
use Modules\Gold\Infrastructure\Commands\FetchGoldDailyCommand;
use Modules\Gold\Infrastructure\Commands\ImportGoldHistoryCommand;
use Modules\Gold\Infrastructure\Repositories\EloquentGoldPriceRepository;

class GoldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GoldPriceRepositoryInterface::class, EloquentGoldPriceRepository::class);
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

            // Fetch daily at 10:00 AM (Antam updates around 9:30 AM WIB)
            $schedule->command('gold:fetch-daily')
                ->dailyAt('10:00')
                ->withoutOverlapping()
                ->runInBackground();
        });
    }
}
