<?php

namespace Modules\Habit\Domain\Contracts;

use Modules\Habit\Domain\Entities\Habit;

interface HabitRepositoryInterface
{
    public function findById(int $id): ?Habit;

    public function findByUser(int $userId): array;

    public function save(Habit $habit): Habit;

    public function delete(int $id): void;
}
