<?php

namespace Modules\Goal\Application\Actions;

use Modules\Goal\Domain\Contracts\GoalRepositoryInterface;

class DeleteGoalAction
{
    public function __construct(
        private GoalRepositoryInterface $repository,
    ) {}

    public function execute(int $goalId): void
    {
        $this->repository->delete($goalId);
    }
}
