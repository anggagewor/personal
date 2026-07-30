<?php

namespace Modules\Accounting\Application\Actions;

use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\Enums\NormalBalance;

class GetTrialBalanceAction
{
    public function __construct(
        private JournalEntryRepositoryInterface $journalEntryRepository,
        private AccountRepositoryInterface $accountRepository,
    ) {}

    /**
     * @return array{accounts: array, total_debit: float, total_credit: float, is_balanced: bool}
     */
    public function execute(int $userId, ?string $startDate = null, ?string $endDate = null): array
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

        $result = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($accounts as $account) {
            if (! isset($balanceMap[$account->id])) {
                continue;
            }

            $balance = $balanceMap[$account->id];
            $totalDbAmount = (float) ($balance->total_debit ?? 0);
            $totalCrAmount = (float) ($balance->total_credit ?? 0);

            $debitColumn = 0.0;
            $creditColumn = 0.0;

            if ($account->normalBalance === NormalBalance::Debit) {
                $net = $totalDbAmount - $totalCrAmount;

                if ($net >= 0) {
                    $debitColumn = round($net, 2);
                } else {
                    $creditColumn = round(abs($net), 2);
                }
            } else {
                $net = $totalCrAmount - $totalDbAmount;

                if ($net >= 0) {
                    $creditColumn = round($net, 2);
                } else {
                    $debitColumn = round(abs($net), 2);
                }
            }

            $result[] = [
                'account_id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type->value,
                'debit' => $debitColumn,
                'credit' => $creditColumn,
            ];

            $totalDebit += $debitColumn;
            $totalCredit += $creditColumn;
        }

        $totalDebit = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);

        return [
            'accounts' => $result,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => bccomp((string) $totalDebit, (string) $totalCredit, 2) === 0,
        ];
    }
}
