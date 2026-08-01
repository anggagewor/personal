<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;

class Refund
{
    public function __construct(
        public ?int $id,
        public int $transactionId,
        public string $refundNumber,
        public float $refundAmount,
        public string $reason,
        public ?string $refundMethod = null,
        /** @var RefundItem[] */
        public array $items = [],
        public ?DateTimeImmutable $createdAt = null,
    ) {}
}
