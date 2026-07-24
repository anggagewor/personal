<?php

namespace Modules\Bookmark\Application\Actions;

use Modules\Bookmark\Domain\Contracts\BookmarkRepositoryInterface;

class DeleteBookmarkAction
{
    public function __construct(
        private BookmarkRepositoryInterface $repository,
    ) {}

    public function execute(int $bookmarkId): void
    {
        $this->repository->delete($bookmarkId);
    }
}
