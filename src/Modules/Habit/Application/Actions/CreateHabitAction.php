<?php

namespace Modules\Habit\Application\Actions;

use Modules\Habit\Application\DTO\HabitData;
use Modules\Habit\Domain\Contracts\HabitRepositoryInterface;
use Modules\Habit\Domain\Entities\Habit;

class CreateHabitAction
{
    public function __construct(
        private HabitRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, HabitData $data): Habit
    {
        $habit = new Habit(
            id: null,
            userId: $userId,
            name: $data->name,
            frequency: $data->frequency,
        );

        return $this->repository->save($habit);
    }
}
