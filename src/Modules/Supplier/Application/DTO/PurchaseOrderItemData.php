<?php

namespace Modules\Supplier\Application\DTO;

readonly class PurchaseOrderItemData
{
    public function __construct(
        public int $productVariantId,
        public string $productName,
        public string $variantName,
        public int $quantity,
        public float $unitCost,
    ) {}
}
