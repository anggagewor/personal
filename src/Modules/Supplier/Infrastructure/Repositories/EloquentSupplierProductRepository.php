<?php

namespace Modules\Supplier\Infrastructure\Repositories;

use Modules\Supplier\Application\DTO\SupplierProductData;
use Modules\Supplier\Domain\Contracts\SupplierProductRepositoryInterface;
use Modules\Supplier\Domain\Entities\SupplierProduct;
use Modules\Supplier\Infrastructure\Models\SupplierProductModel;

class EloquentSupplierProductRepository implements SupplierProductRepositoryInterface
{
    /**
     * @return SupplierProduct[]
     */
    public function findBySupplier(int $supplierId): array
    {
        return SupplierProductModel::where('supplier_id', $supplierId)
            ->get()
            ->map(fn (SupplierProductModel $model) => $this->toEntity($model))
            ->all();
    }

    /**
     * @return SupplierProduct[]
     */
    public function findByProductVariant(int $productVariantId): array
    {
        return SupplierProductModel::where('product_variant_id', $productVariantId)
            ->get()
            ->map(fn (SupplierProductModel $model) => $this->toEntity($model))
            ->all();
    }

    public function link(int $supplierId, SupplierProductData $data): SupplierProduct
    {
        $model = SupplierProductModel::create([
            'supplier_id' => $supplierId,
            'product_variant_id' => $data->productVariantId,
            'default_unit_cost' => $data->defaultUnitCost,
        ]);

        return $this->toEntity($model);
    }

    public function unlink(int $supplierId, int $productVariantId): void
    {
        SupplierProductModel::where('supplier_id', $supplierId)
            ->where('product_variant_id', $productVariantId)
            ->delete();
    }

    public function getDefaultCost(int $supplierId, int $productVariantId): ?float
    {
        $model = SupplierProductModel::where('supplier_id', $supplierId)
            ->where('product_variant_id', $productVariantId)
            ->first();

        if (!$model) {
            return null;
        }

        return $model->default_unit_cost !== null
            ? (float) $model->default_unit_cost
            : null;
    }

    private function toEntity(SupplierProductModel $model): SupplierProduct
    {
        return new SupplierProduct(
            id: $model->id,
            supplierId: $model->supplier_id,
            productVariantId: $model->product_variant_id,
            defaultUnitCost: $model->default_unit_cost !== null
                ? (float) $model->default_unit_cost
                : null,
        );
    }
}
