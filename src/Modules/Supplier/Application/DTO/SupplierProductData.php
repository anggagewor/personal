<?php

namespace Modules\Supplier\Application\DTO;

readonly class SupplierProductData
{
    public function __construct(
        public int $productVariantId,
        public ?float $defaultUnitCost = null,
    ) {}
}
