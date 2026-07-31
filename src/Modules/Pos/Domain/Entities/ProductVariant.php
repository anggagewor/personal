<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;

class ProductVariant
{
    public function __construct(
        public ?int $id,
        public int $productId,
        public string $name,
        public ?string $sku = null,
        public float $price = 0,
        public int $stockQuantity = 0,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
