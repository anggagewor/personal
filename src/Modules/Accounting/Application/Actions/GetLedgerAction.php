<?php

namespace Modules\Accounting\Application\Actions;

use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\Enums\NormalBalance;

class GetLedgerAction
{
    public function __construct(
        private JournalEntryRepositoryInterface $journalEntryRepository,
        private AccountRepositoryInterface $accountRepository,
    ) {}

    /**
     * @return array{opening_balance: float, lines: array}
     */
    public function execute(int $accountId, ?string $startDate = null, ?string $endDate = null): array
    {
        $account = $this->accountRepository->findById($accountId);

        if ($account === null) {
            throw new \DomainException("Akun dengan ID {$accountId} tidak ditemukan.");
        }

        $isDebitNormal = $account->normalBalance === NormalBalance::Debit;

        $openingBalance = $this->computeOpeningBalance($accountId, $startDate, $isDebitNormal);

        $ledgerLines = $this->journalEntryRepository->getLedgerForAccount($accountId, $startDate, $endDate);

        $runningBalance = $openingBalance;
        $lines = [];

        foreach ($ledgerLines as $line) {
            $debit = (float) ($line->debit ?? 0);
            $credit = (float) ($line->credit ?? 0);

            if ($isDebitNormal) {
                $runningBalance = round($runningBalance + $debit - $credit, 2);
            } else {
                $runningBalance = round($runningBalance + $credit - $debit, 2);
            }

            $lines[] = [
                'date' => $line->date,
                'entry_number' => $line->entry_number,
                'description' => $line->description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $runningBalance,
            ];
        }

        return [
            'opening_balance' => $openingBalance,
            'lines' => $lines,
        ];
    }

    private function computeOpeningBalance(int $accountId, ?string $startDate, bool $isDebitNormal): float
    {
        if ($startDate === null) {
            return 0.0;
        }

        $beforeStartDate = date('Y-m-d', strtotime($startDate . ' -1 day'));

        $priorLines = $this->journalEntryRepository->getLedgerForAccount($accountId, null, $beforeStartDate);

        $opening = 0.0;

        foreach ($priorLines as $line) {
            $debit = (float) ($line->debit ?? 0);
            $credit = (float) ($line->credit ?? 0);

            if ($isDebitNormal) {
                $opening += $debit - $credit;
            } else {
                $opening += $credit - $debit;
            }
        }

        return round($opening, 2);
    }
}
