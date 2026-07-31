<?php

namespace Modules\Supplier\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Supplier\Domain\Contracts\GoodsReceiptRepositoryInterface;
use Modules\Supplier\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Supplier\Domain\Contracts\SupplierPaymentRepositoryInterface;
use Modules\Supplier\Domain\Contracts\SupplierProductRepositoryInterface;
use Modules\Supplier\Domain\Contracts\SupplierRepositoryInterface;
use Modules\Supplier\Infrastructure\Repositories\EloquentGoodsReceiptRepository;
use Modules\Supplier\Infrastructure\Repositories\EloquentPurchaseOrderRepository;
use Modules\Supplier\Infrastructure\Repositories\EloquentSupplierPaymentRepository;
use Modules\Supplier\Infrastructure\Repositories\EloquentSupplierProductRepository;
use Modules\Supplier\Infrastructure\Repositories\EloquentSupplierRepository;

class SupplierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SupplierRepositoryInterface::class, EloquentSupplierRepository::class);
        $this->app->bind(PurchaseOrderRepositoryInterface::class, EloquentPurchaseOrderRepository::class);
        $this->app->bind(GoodsReceiptRepositoryInterface::class, EloquentGoodsReceiptRepository::class);
        $this->app->bind(SupplierPaymentRepositoryInterface::class, EloquentSupplierPaymentRepository::class);
        $this->app->bind(SupplierProductRepositoryInterface::class, EloquentSupplierProductRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
