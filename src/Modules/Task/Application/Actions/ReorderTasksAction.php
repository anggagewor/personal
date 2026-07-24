<?php

namespace Modules\Task\Application\Actions;

use Modules\Task\Domain\Contracts\TaskRepositoryInterface;

class ReorderTasksAction
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, array $orderedIds): void
    {
        $this->repository->reorder($userId, $orderedIds);
    }
}
