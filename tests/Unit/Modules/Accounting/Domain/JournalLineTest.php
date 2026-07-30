<?php

namespace Tests\Unit\Modules\Accounting\Domain;

use Modules\Accounting\Domain\ValueObjects\JournalLine;
use PHPUnit\Framework\TestCase;

class JournalLineTest extends TestCase
{
    public function test_debit_line_is_debit(): void
    {
        $line = new JournalLine(id: 1, accountId: 1, debit: 100.00, credit: 0);

        $this->assertTrue($line->isDebit());
        $this->assertFalse($line->isCredit());
        $this->assertEquals(100.00, $line->amount());
    }

    public function test_credit_line_is_credit(): void
    {
        $line = new JournalLine(id: 1, accountId: 1, debit: 0, credit: 250.50);

        $this->assertFalse($line->isDebit());
        $this->assertTrue($line->isCredit());
        $this->assertEquals(250.50, $line->amount());
    }

    public function test_zero_line_is_neither_debit_nor_credit(): void
    {
        $line = new JournalLine(id: 1, accountId: 1, debit: 0, credit: 0);

        $this->assertFalse($line->isDebit());
        $this->assertFalse($line->isCredit());
        $this->assertEquals(0.0, $line->amount());
    }
}
