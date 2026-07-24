<?php

namespace Modules\Habit\Application\Actions;

use Modules\Habit\Application\DTO\HabitData;
use Modules\Habit\Domain\Contracts\HabitRepositoryInterface;
use Modules\Habit\Domain\Entities\Habit;

class UpdateHabitAction
{
    public function __construct(
        private HabitRepositoryInterface $repository,
    ) {}

    public function execute(int $habitId, HabitData $data): Habit
    {
        $habit = $this->repository->findById($habitId);

        $habit->name = $data->name;
        $habit->frequency = $data->frequency;

        return $this->repository->save($habit);
    }
}
