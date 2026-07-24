<?php

namespace Modules\Finance\Domain\Entities;

use DateTimeImmutable;
use Modules\Finance\Domain\Enums\FinanceType;

class Finance
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public FinanceType $type,
        public float $amount,
        public ?string $category = null,
        public ?string $description = null,
        public ?DateTimeImmutable $date = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {}
}
