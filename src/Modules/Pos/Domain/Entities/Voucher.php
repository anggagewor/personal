<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;
use Modules\Pos\Domain\Enums\DiscountType;

class Voucher
{
    public function __construct(
        public ?int $id,
        public int $outletId,
        public string $code,
        public DiscountType $type,
        public float $value,
        public ?float $minPurchase = null,
        public ?int $usageLimit = null,
        public int $usageCount = 0,
        public ?DateTimeImmutable $expiresAt = null,
        public bool $isActive = true,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
