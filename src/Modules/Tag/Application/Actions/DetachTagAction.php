<?php

namespace Modules\Tag\Application\Actions;

use Modules\Tag\Domain\Contracts\TagRepositoryInterface;

class DetachTagAction
{
    public function __construct(
        private TagRepositoryInterface $repository,
    ) {}

    public function execute(int $tagId, string $taggableType, int $taggableId): void
    {
        $this->repository->detach($tagId, $taggableType, $taggableId);
    }
}
