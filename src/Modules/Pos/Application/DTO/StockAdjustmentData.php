<?php

namespace Modules\Pos\Application\DTO;

readonly class StockAdjustmentData
{
    public function __construct(
        public int $productVariantId,
        public string $type,
        public int $quantity,
        public ?string $reason = null,
    ) {}
}
