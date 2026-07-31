<?php

namespace Modules\Pos\Application\DTO;

readonly class LineItemData
{
    public function __construct(
        public int $productId,
        public ?int $productVariantId = null,
        public int $quantity = 1,
        public float $unitPrice = 0,
        public string $productName = '',
        public ?string $variantName = null,
    ) {}
}
