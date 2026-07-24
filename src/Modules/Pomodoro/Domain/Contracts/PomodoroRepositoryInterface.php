<?php

namespace Modules\Pomodoro\Domain\Contracts;

use Modules\Pomodoro\Domain\Entities\Pomodoro;

interface PomodoroRepositoryInterface
{
    public function findById(int $id): ?Pomodoro;

    public function findByUserPaginated(int $userId, int $perPage = 15): array;

    public function save(Pomodoro $pomodoro): Pomodoro;

    public function getStats(int $userId): array;
}
