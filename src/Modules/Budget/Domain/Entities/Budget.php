<?php

namespace Modules\Budget\Domain\Entities;

use DateTimeImmutable;

class Budget
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $category,
        public float $amount,
        public string $month, // format: Y-m (e.g. 2026-07)
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public function isExceeded(float $spent): bool
    {
        return $spent > $this->amount;
    }

    public function isNearLimit(float $spent, float $threshold = 0.8): bool
    {
        return $spent >= ($this->amount * $threshold);
    }

    public function remainingAmount(float $spent): float
    {
        return max(0, $this->amount - $spent);
    }

    public function percentUsed(float $spent): float
    {
        if ($this->amount <= 0) return 0;
        return min(100, ($spent / $this->amount) * 100);
    }
}
