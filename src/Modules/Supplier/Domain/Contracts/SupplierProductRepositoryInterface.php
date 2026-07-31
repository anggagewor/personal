<?php

namespace Modules\Supplier\Domain\Contracts;

use Modules\Supplier\Application\DTO\SupplierProductData;
use Modules\Supplier\Domain\Entities\SupplierProduct;

interface SupplierProductRepositoryInterface
{
    /**
     * @return SupplierProduct[]
     */
    public function findBySupplier(int $supplierId): array;

    /**
     * @return SupplierProduct[]
     */
    public function findByProductVariant(int $productVariantId): array;

    public function link(int $supplierId, SupplierProductData $data): SupplierProduct;

    public function unlink(int $supplierId, int $productVariantId): void;

    public function getDefaultCost(int $supplierId, int $productVariantId): ?float;
}
