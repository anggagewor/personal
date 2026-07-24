<?php

namespace Modules\Tag\Application\Actions;

use Modules\Tag\Domain\Contracts\TagRepositoryInterface;

class DeleteTagAction
{
    public function __construct(
        private TagRepositoryInterface $repository,
    ) {}

    public function execute(int $tagId): void
    {
        $this->repository->delete($tagId);
    }
}
