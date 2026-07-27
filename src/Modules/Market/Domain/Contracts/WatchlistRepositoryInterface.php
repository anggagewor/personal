<?php

namespace Modules\Market\Domain\Contracts;

use Modules\Market\Domain\Entities\WatchlistItem;

interface WatchlistRepositoryInterface
{
    public function findById(int $id): ?WatchlistItem;

    /** @return WatchlistItem[] */
    public function findByUser(int $userId): array;

    public function countByUser(int $userId): int;

    public function save(WatchlistItem $item): WatchlistItem;

    public function delete(int $id): void;
}
