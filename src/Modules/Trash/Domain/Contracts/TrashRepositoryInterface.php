<?php

namespace Modules\Trash\Domain\Contracts;

use Modules\Trash\Domain\Entities\TrashItem;

interface TrashRepositoryInterface
{
    /** @return TrashItem[] */
    public function getAll(int $userId): array;

    public function restore(string $type, int $id, int $userId): bool;

    public function forceDelete(string $type, int $id, int $userId): bool;
}
