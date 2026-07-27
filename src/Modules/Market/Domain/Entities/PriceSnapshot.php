<?php

namespace Modules\Market\Domain\Entities;

use DateTimeImmutable;

class PriceSnapshot
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $symbol,
        public float $price,
        public float $change = 0,
        public float $changePercent = 0,
        public ?float $previousClose = null,
        public ?DateTimeImmutable $fetchedAt = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {}
}
