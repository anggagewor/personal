<?php

namespace Modules\Accounting\Application\Actions;

use DateTimeImmutable;
use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\ValueObjects\JournalLine;

class LoadSampleEntriesAction
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private JournalEntryRepositoryInterface $journalEntryRepository,
    ) {}

    /**
     * Create sample journal entries demonstrating different transaction types.
     * Returns the count of entries created.
     */
    public function execute(int $userId): int
    {
        $accounts = $this->accountRepository->findByUser($userId);
        $accountMap = [];

        foreach ($accounts as $account) {
            $accountMap[$account->code] = $account->id;
        }

        $samples = $this->getSampleEntries($accountMap);
        $created = 0;

        foreach ($samples as $sample) {
            if ($sample === null) {
                continue;
            }

            $entryNumber = $this->journalEntryRepository->getNextEntryNumber($userId);

            $entry = new JournalEntry(
                id: null,
                userId: $userId,
                entryNumber: $entryNumber,
                date: $sample['date'],
                description: $sample['description'],
                lines: $sample['lines'],
            );

            if ($entry->isBalanced()) {
                $this->journalEntryRepository->save($entry);
                $created++;
            }
        }

        return $created;
    }

    /**
     * @param array<string, int> $accountMap Map of account code => account ID
     * @return array<int, array{date: DateTimeImmutable, description: string, lines: JournalLine[]}|null>
     */
    private function getSampleEntries(array $accountMap): array
    {
        $now = new DateTimeImmutable();
        $year = $now->format('Y');
        $month = $now->format('m');

        return [
            // 1. Revenue — Pendapatan jasa diterima kas
            $this->buildEntry(
                $accountMap,
                new DateTimeImmutable("{$year}-{$month}-03"),
                'Pendapatan jasa diterima kas',
                [
                    ['code' => '1000', 'debit' => 5000000, 'credit' => 0],
                    ['code' => '4000', 'debit' => 0, 'credit' => 5000000],
                ]
            ),
            // 2. Expense — Pembayaran gaji karyawan
            $this->buildEntry(
                $accountMap,
                new DateTimeImmutable("{$year}-{$month}-07"),
                'Pembayaran gaji karyawan',
                [
                    ['code' => '5000', 'debit' => 2000000, 'credit' => 0],
                    ['code' => '1000', 'debit' => 0, 'credit' => 2000000],
                ]
            ),
            // 3. Asset purchase — Pembelian peralatan
            $this->buildEntry(
                $accountMap,
                new DateTimeImmutable("{$year}-{$month}-12"),
                'Pembelian peralatan',
                [
                    ['code' => '1400', 'debit' => 3000000, 'credit' => 0],
                    ['code' => '1000', 'debit' => 0, 'credit' => 3000000],
                ]
            ),
            // 4. Liability payment — Pembayaran utang usaha
            $this->buildEntry(
                $accountMap,
                new DateTimeImmutable("{$year}-{$month}-18"),
                'Pembayaran utang usaha',
                [
                    ['code' => '2000', 'debit' => 1000000, 'credit' => 0],
                    ['code' => '1100', 'debit' => 0, 'credit' => 1000000],
                ]
            ),
            // 5. Equity investment — Investasi modal pemilik
            $this->buildEntry(
                $accountMap,
                new DateTimeImmutable("{$year}-{$month}-01"),
                'Investasi modal pemilik',
                [
                    ['code' => '1100', 'debit' => 10000000, 'credit' => 0],
                    ['code' => '3000', 'debit' => 0, 'credit' => 10000000],
                ]
            ),
        ];
    }

    /**
     * Build a single entry from account codes. Returns null if any required account is missing.
     *
     * @param array<string, int> $accountMap
     * @param array<int, array{code: string, debit: float, credit: float}> $lineDefs
     * @return array{date: DateTimeImmutable, description: string, lines: JournalLine[]}|null
     */
    private function buildEntry(
        array $accountMap,
        DateTimeImmutable $date,
        string $description,
        array $lineDefs,
    ): ?array {
        $lines = [];

        foreach ($lineDefs as $lineDef) {
            if (!isset($accountMap[$lineDef['code']])) {
                return null;
            }

            $lines[] = new JournalLine(
                id: null,
                accountId: $accountMap[$lineDef['code']],
                debit: $lineDef['debit'],
                credit: $lineDef['credit'],
            );
        }

        return [
            'date' => $date,
            'description' => $description,
            'lines' => $lines,
        ];
    }
}
