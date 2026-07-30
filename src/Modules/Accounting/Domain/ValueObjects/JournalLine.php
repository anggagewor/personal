<?php

namespace Modules\Accounting\Domain\ValueObjects;

class JournalLine
{
    public function __construct(
        public ?int $id,
        public int $accountId,
        public float $debit,
        public float $credit,
    ) {}

    public function isDebit(): bool
    {
        return $this->debit > 0;
    }

    public function isCredit(): bool
    {
        return $this->credit > 0;
    }

    public function amount(): float
    {
        return $this->isDebit() ? $this->debit : $this->credit;
    }
}
