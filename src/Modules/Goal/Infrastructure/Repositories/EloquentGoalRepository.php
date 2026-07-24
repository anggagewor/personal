<?php

namespace Modules\Goal\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Goal\Domain\Contracts\GoalRepositoryInterface;
use Modules\Goal\Domain\Entities\Goal;
use Modules\Goal\Domain\Enums\GoalStatus;
use Modules\Goal\Infrastructure\Models\GoalModel;

class EloquentGoalRepository implements GoalRepositoryInterface
{
    public function findById(int $id): ?Goal
    {
        $model = GoalModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUser(int $userId): array
    {
        $models = GoalModel::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->toArray();
    }

    public function save(Goal $goal): Goal
    {
        $model = GoalModel::updateOrCreate(
            ['id' => $goal->id],
            [
                'user_id' => $goal->userId,
                'title' => $goal->title,
                'description' => $goal->description,
                'target_date' => $goal->targetDate?->format('Y-m-d'),
                'status' => $goal->status->value,
                'progress' => $goal->progress,
                'milestones' => $goal->milestones,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        GoalModel::where('id', $id)->delete();
    }

    private function toEntity(GoalModel $model): Goal
    {
        return new Goal(
            id: $model->id,
            userId: $model->user_id,
            title: $model->title,
            description: $model->description,
            targetDate: $model->target_date ? new DateTimeImmutable($model->target_date->format('Y-m-d')) : null,
            status: GoalStatus::from($model->status),
            progress: (int) $model->progress,
            milestones: $model->milestones ?? [],
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
