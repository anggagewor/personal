<?php

namespace Modules\Habit\Application\Actions;

use Modules\Habit\Domain\Contracts\HabitRepositoryInterface;
use Modules\Habit\Domain\Entities\Habit;

class ToggleHabitAction
{
    public function __construct(
        private HabitRepositoryInterface $repository,
    ) {}

    public function execute(int $habitId): Habit
    {
        $habit = $this->repository->findById($habitId);
        $habit->toggle();

        return $this->repository->save($habit);
    }
}
