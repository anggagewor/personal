<?php

namespace Modules\Accounting\Application\Actions;

use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\Enums\AccountType;
use Modules\Accounting\Domain\Enums\NormalBalance;

class GetIncomeStatementAction
{
    public function __construct(
        private JournalEntryRepositoryInterface $journalEntryRepository,
        private AccountRepositoryInterface $accountRepository,
    ) {}

    /**
     * @return array{revenue: array, expense: array, net_income: float, label: string}
     */
    public function execute(int $userId, string $startDate, string $endDate): array
    {
        $balances = $this->journalEntryRepository->getAccountBalances($userId, $startDate, $endDate);
        $accounts = $this->accountRepository->findByUser($userId);

        $accountMap = [];
        foreach ($accounts as $account) {
            $accountMap[$account->id] = $account;
        }

        $balanceMap = [];
        foreach ($balances as $balance) {
            $balanceMap[$balance->account_id] = $balance;
        }

        $revenueAccounts = [];
        $totalRevenue = 0.0;

        $expenseAccounts = [];
        $totalExpense = 0.0;

        foreach ($accounts as $account) {
            if (! isset($balanceMap[$account->id])) {
                continue;
            }

            $balance = $balanceMap[$account->id];
            $totalDebit = (float) ($balance->total_debit ?? 0);
            $totalCredit = (float) ($balance->total_credit ?? 0);

            if ($account->type === AccountType::Revenue) {
                $net = round($totalCredit - $totalDebit, 2);
                $revenueAccounts[] = [
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => $net,
                ];
                $totalRevenue += $net;
            } elseif ($account->type === AccountType::Expense) {
                $net = round($totalDebit - $totalCredit, 2);
                $expenseAccounts[] = [
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => $net,
                ];
                $totalExpense += $net;
            }
        }

        $totalRevenue = round($totalRevenue, 2);
        $totalExpense = round($totalExpense, 2);
        $netIncome = round($totalRevenue - $totalExpense, 2);

        $label = match (true) {
            $netIncome > 0 => 'Laba Bersih',
            $netIncome < 0 => 'Rugi Bersih',
            default => 'Impas',
        };

        return [
            'revenue' => [
                'accounts' => $revenueAccounts,
                'total' => $totalRevenue,
            ],
            'expense' => [
                'accounts' => $expenseAccounts,
                'total' => $totalExpense,
            ],
            'net_income' => $netIncome,
            'label' => $label,
        ];
    }
}
