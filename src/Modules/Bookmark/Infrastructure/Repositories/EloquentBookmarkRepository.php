<?php

namespace Modules\Bookmark\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Bookmark\Domain\Contracts\BookmarkRepositoryInterface;
use Modules\Bookmark\Domain\Entities\Bookmark;
use Modules\Bookmark\Infrastructure\Models\BookmarkModel;

class EloquentBookmarkRepository implements BookmarkRepositoryInterface
{
    public function findById(int $id): ?Bookmark
    {
        $model = BookmarkModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUserGroupedByCategory(int $userId): array
    {
        $bookmarks = BookmarkModel::where('user_id', $userId)
            ->orderBy('category')
            ->orderByDesc('created_at')
            ->get();

        $grouped = [];
        foreach ($bookmarks as $model) {
            $category = $model->category ?? 'Uncategorized';
            $grouped[$category][] = $this->toEntity($model);
        }

        return $grouped;
    }

    public function save(Bookmark $bookmark): Bookmark
    {
        $model = BookmarkModel::updateOrCreate(
            ['id' => $bookmark->id],
            [
                'user_id' => $bookmark->userId,
                'title' => $bookmark->title,
                'url' => $bookmark->url,
                'description' => $bookmark->description,
                'category' => $bookmark->category,
                'icon' => $bookmark->icon,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        BookmarkModel::where('id', $id)->delete();
    }

    private function toEntity(BookmarkModel $model): Bookmark
    {
        return new Bookmark(
            id: $model->id,
            userId: $model->user_id,
            title: $model->title,
            url: $model->url,
            description: $model->description,
            category: $model->category,
            icon: $model->icon,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
