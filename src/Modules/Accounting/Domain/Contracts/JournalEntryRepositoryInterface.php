<?php

namespace Modules\Accounting\Domain\Contracts;

use Modules\Accounting\Domain\Entities\JournalEntry;

interface JournalEntryRepositoryInterface
{
    public function findById(int $id): ?JournalEntry;

    public function findByUserPaginated(int $userId, int $perPage = 15): array;

    public function getNextEntryNumber(int $userId): int;

    public function save(JournalEntry $entry): JournalEntry;

    public function delete(int $id): void;

    public function getLedgerForAccount(int $accountId, ?string $startDate = null, ?string $endDate = null): array;

    public function getAccountBalances(int $userId, ?string $startDate = null, ?string $endDate = null): array;

    public function deleteAllByUser(int $userId): void;
}
