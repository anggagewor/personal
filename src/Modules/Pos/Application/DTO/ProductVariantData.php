<?php

namespace Modules\Pos\Application\DTO;

readonly class ProductVariantData
{
    public function __construct(
        public string $name,
        public float $price,
        public ?string $sku = null,
        public int $stockQuantity = 0,
    ) {}
}
