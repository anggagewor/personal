<?php

namespace Modules\Goal\Application\Actions;

use Modules\Goal\Domain\Contracts\GoalRepositoryInterface;
use Modules\Goal\Domain\Entities\Goal;

class ToggleMilestoneAction
{
    public function __construct(
        private GoalRepositoryInterface $repository,
    ) {}

    public function execute(int $goalId, int $milestoneId): Goal
    {
        $goal = $this->repository->findById($goalId);
        $goal->toggleMilestone($milestoneId);

        return $this->repository->save($goal);
    }
}
