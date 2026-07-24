<?php

namespace Modules\Goal\Application\Actions;

use Modules\Goal\Domain\Contracts\GoalRepositoryInterface;
use Modules\Goal\Domain\Entities\Goal;

class AddMilestoneAction
{
    public function __construct(
        private GoalRepositoryInterface $repository,
    ) {}

    public function execute(int $goalId, string $title): Goal
    {
        $goal = $this->repository->findById($goalId);
        $goal->addMilestone($title);

        return $this->repository->save($goal);
    }
}
