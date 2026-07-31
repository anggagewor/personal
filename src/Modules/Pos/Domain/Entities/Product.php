<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;
use Modules\Pos\Domain\Enums\ProductStatus;

class Product
{
    public function __construct(
        public ?int $id,
        public int $outletId,
        public int $categoryId,
        public string $name,
        public float $basePrice,
        public ProductStatus $status = ProductStatus::Active,
        public ?string $sku = null,
        public ?string $image = null,
        public bool $hasVariants = false,
        public bool $trackStock = true,
        /** @var ProductVariant[] */
        public array $variants = [],
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function isActive(): bool
    {
        return $this->status === ProductStatus::Active;
    }
}
