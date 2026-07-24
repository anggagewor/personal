<?php

namespace Modules\Task\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Task\Domain\Contracts\TaskRepositoryInterface;
use Modules\Task\Domain\Entities\Task;
use Modules\Task\Domain\Enums\TaskPriority;
use Modules\Task\Domain\Enums\TaskStatus;
use Modules\Task\Infrastructure\Models\TaskModel;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function findById(int $id): ?Task
    {
        $model = TaskModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUserPaginated(int $userId, ?TaskStatus $status = null, ?TaskPriority $priority = null, int $perPage = 15): array
    {
        $query = TaskModel::where('user_id', $userId);

        if ($status) {
            $query->status($status->value);
        }

        if ($priority) {
            $query->priority($priority->value);
        }

        $paginator = $query->ordered()->paginate($perPage);

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

    public function save(Task $task): Task
    {
        $model = TaskModel::updateOrCreate(
            ['id' => $task->id],
            [
                'user_id' => $task->userId,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'due_date' => $task->dueDate?->format('Y-m-d'),
                'position' => $task->position,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        TaskModel::where('id', $id)->delete();
    }

    public function reorder(int $userId, array $orderedIds): void
    {
        foreach ($orderedIds as $position => $id) {
            TaskModel::where('id', $id)
                ->where('user_id', $userId)
                ->update(['position' => $position]);
        }
    }

    private function toEntity(TaskModel $model): Task
    {
        return new Task(
            id: $model->id,
            userId: $model->user_id,
            title: $model->title,
            description: $model->description,
            status: TaskStatus::from($model->status),
            priority: TaskPriority::from($model->priority),
            dueDate: $model->due_date ? new DateTimeImmutable($model->due_date->format('Y-m-d')) : null,
            position: (int) $model->position,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
            deletedAt: $model->deleted_at ? new DateTimeImmutable($model->deleted_at->toDateTimeString()) : null,
        );
    }
}
