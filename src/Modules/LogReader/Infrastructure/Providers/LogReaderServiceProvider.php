<?php

namespace Modules\LogReader\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\LogReader\Domain\Contracts\LogReaderInterface;
use Modules\LogReader\Infrastructure\Services\ReverseFileLogReader;

class LogReaderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LogReaderInterface::class, ReverseFileLogReader::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
    }
}
