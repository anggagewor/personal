<?php

namespace Modules\Tag\Domain\Contracts;

use Modules\Tag\Domain\Entities\Tag;

interface TagRepositoryInterface
{
    public function findById(int $id): ?Tag;

    public function findByUser(int $userId): array;

    public function save(Tag $tag): Tag;

    public function delete(int $id): void;

    public function attach(int $tagId, string $taggableType, int $taggableId): void;

    public function detach(int $tagId, string $taggableType, int $taggableId): void;
}
