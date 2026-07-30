<?php

namespace Tests\Unit\Modules\Accounting\Domain;

use DateTimeImmutable;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\ValueObjects\JournalLine;
use PHPUnit\Framework\TestCase;

class JournalEntryTest extends TestCase
{
    public function test_balanced_entry_returns_true(): void
    {
        $entry = new JournalEntry(
            id: 1,
            userId: 1,
            entryNumber: 1,
            date: new DateTimeImmutable('2024-01-01'),
            description: 'Test entry',
            lines: [
                new JournalLine(id: 1, accountId: 1, debit: 100.00, credit: 0),
                new JournalLine(id: 2, accountId: 2, debit: 0, credit: 100.00),
            ],
        );

        $this->assertTrue($entry->isBalanced());
        $this->assertEquals(0.0, $entry->imbalanceAmount());
    }

    public function test_unbalanced_entry_returns_false(): void
    {
        $entry = new JournalEntry(
            id: 1,
            userId: 1,
            entryNumber: 1,
            date: new DateTimeImmutable('2024-01-01'),
            description: 'Unbalanced entry',
            lines: [
                new JournalLine(id: 1, accountId: 1, debit: 150.00, credit: 0),
                new JournalLine(id: 2, accountId: 2, debit: 0, credit: 100.00),
            ],
        );

        $this->assertFalse($entry->isBalanced());
        $this->assertEquals(50.0, $entry->imbalanceAmount());
    }

    public function test_total_debit_sums_all_debit_lines(): void
    {
        $entry = new JournalEntry(
            id: 1,
            userId: 1,
            entryNumber: 1,
            date: new DateTimeImmutable('2024-01-01'),
            description: 'Multi debit',
            lines: [
                new JournalLine(id: 1, accountId: 1, debit: 75.50, credit: 0),
                new JournalLine(id: 2, accountId: 2, debit: 24.50, credit: 0),
                new JournalLine(id: 3, accountId: 3, debit: 0, credit: 100.00),
            ],
        );

        $this->assertEquals(100.00, $entry->totalDebit());
        $this->assertEquals(100.00, $entry->totalCredit());
        $this->assertTrue($entry->isBalanced());
    }

    public function test_empty_lines_is_balanced_with_zero(): void
    {
        $entry = new JournalEntry(
            id: 1,
            userId: 1,
            entryNumber: 1,
            date: new DateTimeImmutable('2024-01-01'),
            description: 'Empty',
            lines: [],
        );

        $this->assertTrue($entry->isBalanced());
        $this->assertEquals(0.0, $entry->totalDebit());
        $this->assertEquals(0.0, $entry->totalCredit());
    }
}
