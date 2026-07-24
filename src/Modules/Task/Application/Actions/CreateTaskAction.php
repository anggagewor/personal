<?php

namespace Modules\Task\Application\Actions;

use DateTimeImmutable;
use Modules\Task\Application\DTO\TaskData;
use Modules\Task\Domain\Contracts\TaskRepositoryInterface;
use Modules\Task\Domain\Entities\Task;
use Modules\Task\Domain\Enums\TaskPriority;
use Modules\Task\Domain\Enums\TaskStatus;

class CreateTaskAction
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, TaskData $data): Task
    {
        $task = new Task(
            id: null,
            userId: $userId,
            title: $data->title,
            description: $data->description,
            status: TaskStatus::from($data->status),
            priority: TaskPriority::from($data->priority),
            dueDate: $data->dueDate ? new DateTimeImmutable($data->dueDate) : null,
            position: $data->position,
        );

        return $this->repository->save($task);
    }
}
