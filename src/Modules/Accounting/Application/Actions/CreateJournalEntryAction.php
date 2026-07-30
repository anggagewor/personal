<?php

namespace Modules\Accounting\Application\Actions;

use DateTimeImmutable;
use Modules\Accounting\Application\DTO\JournalEntryData;
use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Exceptions\UnbalancedEntryException;
use Modules\Accounting\Domain\ValueObjects\JournalLine;

class CreateJournalEntryAction
{
    public function __construct(
        private JournalEntryRepositoryInterface $journalEntryRepository,
        private AccountRepositoryInterface $accountRepository,
    ) {}

    public function execute(int $userId, JournalEntryData $data): JournalEntry
    {
        $lines = $this->buildLines($data);

        $this->validateLineCount($lines);
        $this->validateAccountsExist($lines);

        $entryNumber = $this->journalEntryRepository->getNextEntryNumber($userId);

        $entry = new JournalEntry(
            id: null,
            userId: $userId,
            entryNumber: $entryNumber,
            date: new DateTimeImmutable($data->date),
            description: $data->description,
            lines: $lines,
        );

        if (! $entry->isBalanced()) {
            throw UnbalancedEntryException::create($entry->imbalanceAmount());
        }

        return $this->journalEntryRepository->save($entry);
    }

    /**
     * @return JournalLine[]
     */
    private function buildLines(JournalEntryData $data): array
    {
        return array_map(
            fn ($lineData) => new JournalLine(
                id: null,
                accountId: $lineData->accountId,
                debit: $lineData->debit,
                credit: $lineData->credit,
            ),
            $data->lines,
        );
    }

    /**
     * @param JournalLine[] $lines
     */
    private function validateLineCount(array $lines): void
    {
        $count = count($lines);

        if ($count < 2) {
            throw new \DomainException('Jurnal harus memiliki minimal 2 baris.');
        }

        if ($count > 20) {
            throw new \DomainException('Jurnal tidak boleh lebih dari 20 baris.');
        }
    }

    /**
     * @param JournalLine[] $lines
     */
    private function validateAccountsExist(array $lines): void
    {
        $accountIds = array_unique(array_map(fn (JournalLine $line) => $line->accountId, $lines));

        foreach ($accountIds as $accountId) {
            if ($this->accountRepository->findById($accountId) === null) {
                throw new \DomainException("Akun dengan ID {$accountId} tidak ditemukan.");
            }
        }
    }
}
