<?php

namespace Modules\Task\Infrastructure\Resources;

use Modules\Task\Domain\Entities\Task;

class TaskResource
{
    public static function toArray(Task $task): array
    {
        return [
            'id' => $task->id,
            'user_id' => $task->userId,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
            'priority' => $task->priority->value,
            'due_date' => $task->dueDate?->format('Y-m-d'),
            'position' => $task->position,
            'created_at' => $task->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'updated_at' => $task->updatedAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $tasks): array
    {
        return array_map(fn (Task $task) => self::toArray($task), $tasks);
    }
}
