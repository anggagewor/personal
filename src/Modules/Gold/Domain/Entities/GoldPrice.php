<?php

namespace Modules\Gold\Domain\Entities;

use DateTimeImmutable;

class GoldPrice
{
    public function __construct(
        public ?int $id,
        public string $date,
        public int $price,
        public int $change = 0,
        public float $changePercent = 0,
        public ?DateTimeImmutable $createdAt = null,
    ) {}
}
