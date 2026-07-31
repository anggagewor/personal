<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;
use Modules\Pos\Domain\Enums\StockAdjustmentType;

class StockAdjustment
{
    public function __construct(
        public ?int $id,
        public int $productVariantId,
        public StockAdjustmentType $type,
        public int $quantity,
        public ?string $reason = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {}
}
