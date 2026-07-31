<?php

namespace Modules\Pos\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Pos\Domain\Contracts\CategoryRepositoryInterface;
use Modules\Pos\Domain\Contracts\DiscountRepositoryInterface;
use Modules\Pos\Domain\Contracts\MemberRepositoryInterface;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;
use Modules\Pos\Domain\Contracts\ReportRepositoryInterface;
use Modules\Pos\Domain\Contracts\TableRepositoryInterface;
use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;
use Modules\Pos\Domain\Contracts\VoucherRepositoryInterface;
use Modules\Pos\Infrastructure\Repositories\EloquentCategoryRepository;
use Modules\Pos\Infrastructure\Repositories\EloquentDiscountRepository;
use Modules\Pos\Infrastructure\Repositories\EloquentMemberRepository;
use Modules\Pos\Infrastructure\Repositories\EloquentOutletRepository;
use Modules\Pos\Infrastructure\Repositories\EloquentProductRepository;
use Modules\Pos\Infrastructure\Repositories\EloquentReportRepository;
use Modules\Pos\Infrastructure\Repositories\EloquentTableRepository;
use Modules\Pos\Infrastructure\Repositories\EloquentTransactionRepository;
use Modules\Pos\Infrastructure\Repositories\EloquentVoucherRepository;

class PosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OutletRepositoryInterface::class, EloquentOutletRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
        $this->app->bind(DiscountRepositoryInterface::class, EloquentDiscountRepository::class);
        $this->app->bind(VoucherRepositoryInterface::class, EloquentVoucherRepository::class);
        $this->app->bind(TableRepositoryInterface::class, EloquentTableRepository::class);
        $this->app->bind(MemberRepositoryInterface::class, EloquentMemberRepository::class);
        $this->app->bind(ReportRepositoryInterface::class, EloquentReportRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        Route::prefix('api')->group(__DIR__ . '/../Routes/public.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
