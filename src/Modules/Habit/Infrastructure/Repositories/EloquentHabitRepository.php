<?php

namespace Modules\Habit\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Habit\Domain\Contracts\HabitRepositoryInterface;
use Modules\Habit\Domain\Entities\Habit;
use Modules\Habit\Infrastructure\Models\HabitModel;

class EloquentHabitRepository implements HabitRepositoryInterface
{
    public function findById(int $id): ?Habit
    {
        $model = HabitModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUser(int $userId): array
    {
        $models = HabitModel::where('user_id', $userId)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->toArray();
    }

    public function save(Habit $habit): Habit
    {
        $model = HabitModel::updateOrCreate(
            ['id' => $habit->id],
            [
                'user_id' => $habit->userId,
                'name' => $habit->name,
                'frequency' => $habit->frequency,
                'current_streak' => $habit->currentStreak,
                'longest_streak' => $habit->longestStreak,
                'last_completed_at' => $habit->lastCompletedAt?->format('Y-m-d H:i:s'),
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        HabitModel::where('id', $id)->delete();
    }

    private function toEntity(HabitModel $model): Habit
    {
        return new Habit(
            id: $model->id,
            userId: $model->user_id,
            name: $model->name,
            frequency: $model->frequency,
            currentStreak: (int) $model->current_streak,
            longestStreak: (int) $model->longest_streak,
            lastCompletedAt: $model->last_completed_at ? new DateTimeImmutable($model->last_completed_at->toDateTimeString()) : null,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
