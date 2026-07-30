<?php

namespace Modules\Accounting\Domain\Contracts;

use Modules\Accounting\Domain\Entities\Account;

interface AccountRepositoryInterface
{
    public function findById(int $id): ?Account;

    public function findByCode(int $userId, string $code): ?Account;

    public function findByUser(int $userId): array;

    public function findByUserGroupedByType(int $userId): array;

    public function getDepth(int $accountId): int;

    public function hasJournalLines(int $accountId): bool;

    public function save(Account $account): Account;

    public function delete(int $id): void;

    public function countByUser(int $userId): int;

    public function deleteAllByUser(int $userId): void;
}
