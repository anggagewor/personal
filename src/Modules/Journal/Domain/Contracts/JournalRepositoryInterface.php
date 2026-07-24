<?php

namespace Modules\Journal\Domain\Contracts;

use Modules\Journal\Domain\Entities\Journal;

interface JournalRepositoryInterface
{
    public function findById(int $id): ?Journal;

    public function findByUserPaginated(int $userId, ?string $month = null, int $perPage = 15): array;

    public function findByDate(int $userId, string $date): ?Journal;

    public function findMoodsByUser(int $userId): array;

    public function save(Journal $journal): Journal;

    public function delete(int $id): void;
}
