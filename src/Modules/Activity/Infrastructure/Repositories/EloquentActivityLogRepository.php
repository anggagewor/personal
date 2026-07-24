<?php

namespace Modules\Activity\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Activity\Domain\Contracts\ActivityLogRepositoryInterface;
use Modules\Activity\Domain\Entities\ActivityLog;
use Modules\Activity\Infrastructure\Models\ActivityLogModel;

class EloquentActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function findByUserPaginated(int $userId, int $perPage = 15): array
    {
        $paginator = ActivityLogModel::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);

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

    public function save(ActivityLog $activityLog): ActivityLog
    {
        $model = ActivityLogModel::create([
            'user_id' => $activityLog->userId,
            'action' => $activityLog->action,
            'description' => $activityLog->description,
            'metadata' => $activityLog->metadata,
        ]);

        return $this->toEntity($model);
    }

    private function toEntity(ActivityLogModel $model): ActivityLog
    {
        return new ActivityLog(
            id: $model->id,
            userId: $model->user_id,
            action: $model->action,
            description: $model->description,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
