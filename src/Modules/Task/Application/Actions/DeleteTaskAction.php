<?php

namespace Modules\Task\Application\Actions;

use Modules\Task\Domain\Contracts\TaskRepositoryInterface;

class DeleteTaskAction
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    public function execute(int $taskId): void
    {
        $this->repository->delete($taskId);
    }
}
