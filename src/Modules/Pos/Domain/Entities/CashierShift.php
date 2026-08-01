<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;
use Modules\Pos\Domain\Enums\ShiftStatus;

class CashierShift
{
    public function __construct(
        public ?int $id,
        public int $outletId,
        public int $userId,
        public string $cashierName,
        public float $openingAmount = 0,
        public ?float $closingAmount = null,
        public ?float $expectedAmount = null,
        public ?float $difference = null,
        public ShiftStatus $status = ShiftStatus::Open,
        public ?string $notes = null,
        public ?DateTimeImmutable $openedAt = null,
        public ?DateTimeImmutable $closedAt = null,
    ) {}

    public function isOpen(): bool
    {
        return $this->status === ShiftStatus::Open;
    }

    public function isClosed(): bool
    {
        return $this->status === ShiftStatus::Closed;
    }
}
