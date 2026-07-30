<?php

namespace Tests\Unit\Modules\Accounting\Domain;

use Modules\Accounting\Domain\Enums\AccountType;
use Modules\Accounting\Domain\Enums\NormalBalance;
use PHPUnit\Framework\TestCase;

class AccountTypeTest extends TestCase
{
    public function test_asset_has_debit_normal_balance(): void
    {
        $this->assertEquals(NormalBalance::Debit, AccountType::Asset->normalBalance());
    }

    public function test_expense_has_debit_normal_balance(): void
    {
        $this->assertEquals(NormalBalance::Debit, AccountType::Expense->normalBalance());
    }

    public function test_liability_has_credit_normal_balance(): void
    {
        $this->assertEquals(NormalBalance::Credit, AccountType::Liability->normalBalance());
    }

    public function test_equity_has_credit_normal_balance(): void
    {
        $this->assertEquals(NormalBalance::Credit, AccountType::Equity->normalBalance());
    }

    public function test_revenue_has_credit_normal_balance(): void
    {
        $this->assertEquals(NormalBalance::Credit, AccountType::Revenue->normalBalance());
    }
}
