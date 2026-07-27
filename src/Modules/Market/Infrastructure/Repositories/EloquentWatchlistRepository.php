<?php

namespace Modules\Market\Infrastructure\Repositories;

use Modules\Market\Domain\Contracts\WatchlistRepositoryInterface;
use Modules\Market\Domain\Entities\WatchlistItem;
use Modules\Market\Infrastructure\Models\WatchlistItemModel;

class EloquentWatchlistRepository implements WatchlistRepositoryInterface
{
    public function findById(int $id): ?WatchlistItem
    {
        $model = WatchlistItemModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByUser(int $userId): array
    {
        return WatchlistItemModel::where('user_id', $userId)
            ->orderBy('position')
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->all();
    }

    public function countByUser(int $userId): int
    {
        return WatchlistItemModel::where('user_id', $userId)->count();
    }

    public function save(WatchlistItem $item): WatchlistItem
    {
        $model = $item->id
            ? WatchlistItemModel::findOrFail($item->id)
            : new WatchlistItemModel();

        $model->user_id = $item->userId;
        $model->symbol = $item->symbol;
        $model->type = $item->type;
        $model->label = $item->label;
        $model->position = $item->position;
        $model->save();

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        WatchlistItemModel::destroy($id);
    }

    private function toEntity(WatchlistItemModel $model): WatchlistItem
    {
        return new WatchlistItem(
            id: $model->id,
            userId: $model->user_id,
            symbol: $model->symbol,
            type: $model->type,
            label: $model->label,
            position: $model->position,
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new \DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
