<?php

namespace Modules\Goal\Application\Actions;

use DateTimeImmutable;
use Modules\Goal\Application\DTO\GoalData;
use Modules\Goal\Domain\Contracts\GoalRepositoryInterface;
use Modules\Goal\Domain\Entities\Goal;
use Modules\Goal\Domain\Enums\GoalStatus;

class UpdateGoalAction
{
    public function __construct(
        private GoalRepositoryInterface $repository,
    ) {}

    public function execute(int $goalId, GoalData $data): Goal
    {
        $goal = $this->repository->findById($goalId);

        $goal->title = $data->title;
        $goal->description = $data->description;
        $goal->targetDate = $data->targetDate ? new DateTimeImmutable($data->targetDate) : null;
        $goal->status = GoalStatus::from($data->status);

        return $this->repository->save($goal);
    }
}
