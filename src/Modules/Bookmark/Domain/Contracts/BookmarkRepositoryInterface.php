<?php

namespace Modules\Bookmark\Domain\Contracts;

use Modules\Bookmark\Domain\Entities\Bookmark;

interface BookmarkRepositoryInterface
{
    public function findById(int $id): ?Bookmark;

    public function findByUserGroupedByCategory(int $userId): array;

    public function save(Bookmark $bookmark): Bookmark;

    public function delete(int $id): void;
}
