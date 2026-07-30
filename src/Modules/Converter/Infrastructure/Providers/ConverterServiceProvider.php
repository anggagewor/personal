<?php

namespace Modules\Converter\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Converter\Domain\Contracts\CustomCategoryRepositoryInterface;
use Modules\Converter\Domain\Contracts\CustomUnitRepositoryInterface;
use Modules\Converter\Infrastructure\Repositories\EloquentCustomCategoryRepository;
use Modules\Converter\Infrastructure\Repositories\EloquentCustomUnitRepository;

class ConverterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CustomCategoryRepositoryInterface::class, EloquentCustomCategoryRepository::class);
        $this->app->bind(CustomUnitRepositoryInterface::class, EloquentCustomUnitRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
    }
}
