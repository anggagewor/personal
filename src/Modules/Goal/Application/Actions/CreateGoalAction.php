<?php

namespace Modules\Goal\Application\Actions;

use DateTimeImmutable;
use Modules\Goal\Application\DTO\GoalData;
use Modules\Goal\Domain\Contracts\GoalRepositoryInterface;
use Modules\Goal\Domain\Entities\Goal;
use Modules\Goal\Domain\Enums\GoalStatus;

class CreateGoalAction
{
    public function __construct(
        private GoalRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, GoalData $data): Goal
    {
        $goal = new Goal(
            id: null,
            userId: $userId,
            title: $data->title,
            description: $data->description,
            targetDate: $data->targetDate ? new DateTimeImmutable($data->targetDate) : null,
            status: GoalStatus::from($data->status),
        );

        foreach ($data->milestones as $milestone) {
            $goal->addMilestone($milestone['title']);
        }

        return $this->repository->save($goal);
    }
}
