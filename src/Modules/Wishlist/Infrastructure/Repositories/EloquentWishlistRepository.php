<?php

namespace Modules\Wishlist\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Wishlist\Domain\Contracts\WishlistRepositoryInterface;
use Modules\Wishlist\Domain\Entities\WishlistItem;
use Modules\Wishlist\Infrastructure\Models\WishlistItemModel;

class EloquentWishlistRepository implements WishlistRepositoryInterface
{
    public function findById(int $id): ?WishlistItem
    {
        $model = WishlistItemModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUserFiltered(int $userId, ?bool $isCompleted = null, ?string $category = null, int $perPage = 15): array
    {
        $query = WishlistItemModel::where('user_id', $userId);

        if ($isCompleted !== null) {
            $query->where('is_completed', $isCompleted);
        }

        if ($category) {
            $query->where('category', $category);
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

    public function save(WishlistItem $item): WishlistItem
    {
        $model = WishlistItemModel::updateOrCreate(
            ['id' => $item->id],
            [
                'user_id' => $item->userId,
                'title' => $item->title,
                'description' => $item->description,
                'category' => $item->category,
                'is_completed' => $item->isCompleted,
                'completed_at' => $item->completedAt?->format('Y-m-d H:i:s'),
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        WishlistItemModel::where('id', $id)->delete();
    }

    private function toEntity(WishlistItemModel $model): WishlistItem
    {
        return new WishlistItem(
            id: $model->id,
            userId: $model->user_id,
            title: $model->title,
            description: $model->description,
            category: $model->category,
            isCompleted: (bool) $model->is_completed,
            completedAt: $model->completed_at ? new DateTimeImmutable($model->completed_at->toDateTimeString()) : null,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
