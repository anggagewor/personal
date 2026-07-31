<?php

namespace Modules\Pos\Application\DTO;

readonly class ProductData
{
    public function __construct(
        public string $name,
        public float $basePrice,
        public int $categoryId,
        public ?string $sku = null,
        public ?string $image = null,
        public bool $hasVariants = false,
        public bool $trackStock = true,
        /** @var ProductVariantData[] */
        public array $variants = [],
    ) {}
}
