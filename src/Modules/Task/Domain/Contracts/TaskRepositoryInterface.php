<?php

namespace Modules\Task\Domain\Contracts;

use Modules\Task\Domain\Entities\Task;
use Modules\Task\Domain\Enums\TaskPriority;
use Modules\Task\Domain\Enums\TaskStatus;

interface TaskRepositoryInterface
{
    public function findById(int $id): ?Task;

    public function findByUserPaginated(int $userId, ?TaskStatus $status = null, ?TaskPriority $priority = null, int $perPage = 15): array;

    public function save(Task $task): Task;

    public function delete(int $id): void;

    public function reorder(int $userId, array $orderedIds): void;
}
