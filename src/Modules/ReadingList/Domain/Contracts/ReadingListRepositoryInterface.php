<?php

namespace Modules\ReadingList\Domain\Contracts;

use Modules\ReadingList\Domain\Entities\ReadingItem;

interface ReadingListRepositoryInterface
{
    public function findById(int $id): ?ReadingItem;

    public function findByUserPaginated(int $userId, ?bool $isRead = null, ?bool $isFavorite = null, int $perPage = 15): array;

    public function save(ReadingItem $item): ReadingItem;

    public function delete(int $id): void;
}
