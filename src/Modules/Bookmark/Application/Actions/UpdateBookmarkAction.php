<?php

namespace Modules\Bookmark\Application\Actions;

use Modules\Bookmark\Application\DTO\BookmarkData;
use Modules\Bookmark\Domain\Contracts\BookmarkRepositoryInterface;
use Modules\Bookmark\Domain\Entities\Bookmark;

class UpdateBookmarkAction
{
    public function __construct(
        private BookmarkRepositoryInterface $repository,
    ) {}

    public function execute(int $bookmarkId, BookmarkData $data): Bookmark
    {
        $bookmark = $this->repository->findById($bookmarkId);

        $bookmark->title = $data->title;
        $bookmark->url = $data->url;
        $bookmark->description = $data->description;
        $bookmark->category = $data->category;
        $bookmark->icon = $data->icon;

        return $this->repository->save($bookmark);
    }
}
