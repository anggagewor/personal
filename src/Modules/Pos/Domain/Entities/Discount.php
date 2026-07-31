<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;
use Modules\Pos\Domain\Enums\DiscountType;

class Discount
{
    public function __construct(
        public ?int $id,
        public int $outletId,
        public string $name,
        public DiscountType $type,
        public float $value,
        public ?float $minPurchase = null,
        public ?int $buyQuantity = null,
        public ?int $getQuantity = null,
        public ?int $productId = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public bool $isActive = true,
        public bool $memberOnly = false,
        public int $priority = 0,
        public ?array $conditions = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
