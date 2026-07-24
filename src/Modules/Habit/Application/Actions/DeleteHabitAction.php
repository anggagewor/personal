<?php

namespace Modules\Habit\Application\Actions;

use Modules\Habit\Domain\Contracts\HabitRepositoryInterface;

class DeleteHabitAction
{
    public function __construct(
        private HabitRepositoryInterface $repository,
    ) {}

    public function execute(int $habitId): void
    {
        $this->repository->delete($habitId);
    }
}
