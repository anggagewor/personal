<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;

class VoucherRedemption
{
    public function __construct(
        public ?int $id,
        public int $voucherId,
        public int $transactionId,
        public float $discountAmount,
        public ?DateTimeImmutable $redeemedAt = null,
    ) {}
}
