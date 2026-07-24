<?php

namespace Modules\Calendar\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Calendar\Domain\Contracts\CalendarEventRepositoryInterface;
use Modules\Calendar\Domain\Contracts\HolidayRepositoryInterface;
use Modules\Calendar\Infrastructure\Repositories\EloquentCalendarEventRepository;
use Modules\Calendar\Infrastructure\Repositories\EloquentHolidayRepository;

class CalendarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CalendarEventRepositoryInterface::class, EloquentCalendarEventRepository::class);
        $this->app->bind(HolidayRepositoryInterface::class, EloquentHolidayRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
