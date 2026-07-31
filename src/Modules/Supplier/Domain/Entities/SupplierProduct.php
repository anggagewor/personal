<?php

namespace Modules\Supplier\Domain\Entities;

class SupplierProduct
{
    public function __construct(
        public ?int $id,
        public int $supplierId,
        public int $productVariantId,
        public ?float $defaultUnitCost = null,
        public ?string $productName = null,
        public ?string $variantName = null,
    ) {}
}
