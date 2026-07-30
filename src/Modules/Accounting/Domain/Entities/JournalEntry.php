<?php

namespace Modules\Accounting\Domain\Entities;

use DateTimeImmutable;
use Modules\Accounting\Domain\ValueObjects\JournalLine;

class JournalEntry
{
    /**
     * @param JournalLine[] $lines
     */
    public function __construct(
        public ?int $id,
        public int $userId,
        public int $entryNumber,
        public DateTimeImmutable $date,
        public string $description,
        public array $lines,
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public function isBalanced(): bool
    {
        return bccomp(
            number_format($this->totalDebit(), 2, '.', ''),
            number_format($this->totalCredit(), 2, '.', ''),
            2
        ) === 0;
    }

    public function totalDebit(): float
    {
        return array_sum(array_map(fn (JournalLine $line) => $line->debit, $this->lines));
    }

    public function totalCredit(): float
    {
        return array_sum(array_map(fn (JournalLine $line) => $line->credit, $this->lines));
    }

    public function imbalanceAmount(): float
    {
        return abs($this->totalDebit() - $this->totalCredit());
    }
}
