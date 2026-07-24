<?php

namespace Modules\ReadingList\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\ReadingList\Domain\Contracts\ReadingListRepositoryInterface;
use Modules\ReadingList\Domain\Entities\ReadingItem;
use Modules\ReadingList\Infrastructure\Models\ReadingItemModel;

class EloquentReadingListRepository implements ReadingListRepositoryInterface
{
    public function findById(int $id): ?ReadingItem
    {
        $model = ReadingItemModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUserPaginated(int $userId, ?bool $isRead = null, ?bool $isFavorite = null, int $perPage = 15): array
    {
        $query = ReadingItemModel::where('user_id', $userId);

        if ($isRead !== null) {
            $query->where('is_read', $isRead);
        }

        if ($isFavorite !== null) {
            $query->where('is_favorite', $isFavorite);
        }

        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function save(ReadingItem $item): ReadingItem
    {
        $model = ReadingItemModel::updateOrCreate(
            ['id' => $item->id],
            [
                'user_id' => $item->userId,
                'title' => $item->title,
                'url' => $item->url,
                'domain' => $item->domain,
                'is_read' => $item->isRead,
                'is_favorite' => $item->isFavorite,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        ReadingItemModel::where('id', $id)->delete();
    }

    private function toEntity(ReadingItemModel $model): ReadingItem
    {
        return new ReadingItem(
            id: $model->id,
            userId: $model->user_id,
            title: $model->title,
            url: $model->url,
            domain: $model->domain,
            isRead: (bool) $model->is_read,
            isFavorite: (bool) $model->is_favorite,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
