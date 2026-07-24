<?php

namespace Modules\Task\Application\Actions;

use DateTimeImmutable;
use Modules\Task\Application\DTO\TaskData;
use Modules\Task\Domain\Contracts\TaskRepositoryInterface;
use Modules\Task\Domain\Entities\Task;
use Modules\Task\Domain\Enums\TaskPriority;
use Modules\Task\Domain\Enums\TaskStatus;

class UpdateTaskAction
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    public function execute(int $taskId, TaskData $data): Task
    {
        $task = $this->repository->findById($taskId);

        $task->title = $data->title;
        $task->description = $data->description;
        $task->status = TaskStatus::from($data->status);
        $task->priority = TaskPriority::from($data->priority);
        $task->dueDate = $data->dueDate ? new DateTimeImmutable($data->dueDate) : null;
        $task->position = $data->position;

        return $this->repository->save($task);
    }
}
