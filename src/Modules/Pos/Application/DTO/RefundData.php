<?php

namespace Modules\Pos\Application\DTO;

readonly class RefundData
{
    public function __construct(
        public int $transactionId,
        /** @var RefundItemData[] */
        public array $items,
        public string $reason,
        public ?string $refundMethod = null,
    ) {}
}
