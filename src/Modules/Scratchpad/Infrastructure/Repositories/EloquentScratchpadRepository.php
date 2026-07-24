<?php

namespace Modules\Scratchpad\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Scratchpad\Domain\Contracts\ScratchpadRepositoryInterface;
use Modules\Scratchpad\Domain\Entities\Scratchpad;
use Modules\Scratchpad\Infrastructure\Models\ScratchpadModel;

class EloquentScratchpadRepository implements ScratchpadRepositoryInterface
{
    public function findById(int $id): ?Scratchpad
    {
        $model = ScratchpadModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUser(int $userId): array
    {
        $models = ScratchpadModel::where('user_id', $userId)
            ->orderBy('position')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->toArray();
    }

    public function save(Scratchpad $scratchpad): Scratchpad
    {
        $model = ScratchpadModel::updateOrCreate(
            ['id' => $scratchpad->id],
            [
                'user_id' => $scratchpad->userId,
                'content' => $scratchpad->content,
                'color' => $scratchpad->color,
                'position' => $scratchpad->position,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        ScratchpadModel::where('id', $id)->delete();
    }

    private function toEntity(ScratchpadModel $model): Scratchpad
    {
        return new Scratchpad(
            id: $model->id,
            userId: $model->user_id,
            content: $model->content,
            color: $model->color,
            position: (int) $model->position,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
