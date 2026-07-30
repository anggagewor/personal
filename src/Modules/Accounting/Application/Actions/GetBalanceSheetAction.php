<?php

namespace Modules\Accounting\Application\Actions;

use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\Enums\AccountType;

class GetBalanceSheetAction
{
    public function __construct(
        private JournalEntryRepositoryInterface $journalEntryRepository,
        private AccountRepositoryInterface $accountRepository,
    ) {}

    /**
     * @return array{assets: array, liabilities: array, equity: array, net_income: float, total_assets: float, total_liabilities: float, total_equity: float, is_balanced: bool}
     */
    public function execute(int $userId, string $asOfDate): array
    {
        $cumulativeBalances = $this->journalEntryRepository->getAccountBalances($userId, null, $asOfDate);
        $accounts = $this->accountRepository->findByUser($userId);

        $accountMap = [];
        foreach ($accounts as $account) {
            $accountMap[$account->id] = $account;
        }

        $balanceMap = [];
        foreach ($cumulativeBalances as $balance) {
            $balanceMap[$balance->account_id] = $balance;
        }

        $assetAccounts = [];
        $totalAssets = 0.0;

        $liabilityAccounts = [];
        $totalLiabilities = 0.0;

        $equityAccounts = [];
        $totalEquityAccounts = 0.0;

        foreach ($accounts as $account) {
            if (! isset($balanceMap[$account->id])) {
                continue;
            }

            $balance = $balanceMap[$account->id];
            $totalDebit = (float) ($balance->total_debit ?? 0);
            $totalCredit = (float) ($balance->total_credit ?? 0);

            if ($account->type === AccountType::Asset) {
                $net = round($totalDebit - $totalCredit, 2);
                $assetAccounts[] = [
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => $net,
                ];
                $totalAssets += $net;
            } elseif ($account->type === AccountType::Liability) {
                $net = round($totalCredit - $totalDebit, 2);
                $liabilityAccounts[] = [
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => $net,
                ];
                $totalLiabilities += $net;
            } elseif ($account->type === AccountType::Equity) {
                $net = round($totalCredit - $totalDebit, 2);
                $equityAccounts[] = [
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => $net,
                ];
                $totalEquityAccounts += $net;
            }
        }

        $netIncome = $this->computeNetIncome($userId, $asOfDate);

        $totalAssets = round($totalAssets, 2);
        $totalLiabilities = round($totalLiabilities, 2);
        $totalEquityAccounts = round($totalEquityAccounts, 2);
        $totalEquity = round($totalEquityAccounts + $netIncome, 2);

        $isBalanced = bccomp((string) $totalAssets, (string) round($totalLiabilities + $totalEquity, 2), 2) === 0;

        return [
            'assets' => $assetAccounts,
            'liabilities' => $liabilityAccounts,
            'equity' => $equityAccounts,
            'net_income' => $netIncome,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'is_balanced' => $isBalanced,
        ];
    }

    private function computeNetIncome(int $userId, string $asOfDate): float
    {
        $fiscalStartDate = date('Y-m-01', strtotime($asOfDate));

        $balances = $this->journalEntryRepository->getAccountBalances($userId, $fiscalStartDate, $asOfDate);
        $accounts = $this->accountRepository->findByUser($userId);

        $accountMap = [];
        foreach ($accounts as $account) {
            $accountMap[$account->id] = $account;
        }

        $totalRevenue = 0.0;
        $totalExpense = 0.0;

        foreach ($balances as $balance) {
            if (! isset($accountMap[$balance->account_id])) {
                continue;
            }

            $account = $accountMap[$balance->account_id];
            $totalDebit = (float) ($balance->total_debit ?? 0);
            $totalCredit = (float) ($balance->total_credit ?? 0);

            if ($account->type === AccountType::Revenue) {
                $totalRevenue += ($totalCredit - $totalDebit);
            } elseif ($account->type === AccountType::Expense) {
                $totalExpense += ($totalDebit - $totalCredit);
            }
        }

        return round($totalRevenue - $totalExpense, 2);
    }
}
