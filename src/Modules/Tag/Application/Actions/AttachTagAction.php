<?php

namespace Modules\Tag\Application\Actions;

use Modules\Tag\Domain\Contracts\TagRepositoryInterface;

class AttachTagAction
{
    public function __construct(
        private TagRepositoryInterface $repository,
    ) {}

    public function execute(int $tagId, string $taggableType, int $taggableId): void
    {
        $this->repository->attach($tagId, $taggableType, $taggableId);
    }
}
