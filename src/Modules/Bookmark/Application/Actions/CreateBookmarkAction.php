<?php

namespace Modules\Bookmark\Application\Actions;

use Modules\Bookmark\Application\DTO\BookmarkData;
use Modules\Bookmark\Domain\Contracts\BookmarkRepositoryInterface;
use Modules\Bookmark\Domain\Entities\Bookmark;

class CreateBookmarkAction
{
    public function __construct(
        private BookmarkRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, BookmarkData $data): Bookmark
    {
        $bookmark = new Bookmark(
            id: null,
            userId: $userId,
            title: $data->title,
            url: $data->url,
            description: $data->description,
            category: $data->category,
            icon: $data->icon,
        );

        return $this->repository->save($bookmark);
    }
}
