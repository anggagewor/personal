<?php

namespace Modules\Goal\Domain\Contracts;

use Modules\Goal\Domain\Entities\Goal;

interface GoalRepositoryInterface
{
    public function findById(int $id): ?Goal;

    public function findByUser(int $userId): array;

    public function save(Goal $goal): Goal;

    public function delete(int $id): void;
}
