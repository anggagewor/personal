<?php

namespace Modules\Budget\Domain\Contracts;

use Modules\Budget\Domain\Entities\Budget;

interface BudgetRepositoryInterface
{
    public function findById(int $id): ?Budget;

    public function findByUserAndMonth(int $userId, string $month): array;

    public function findByUserCategoryMonth(int $userId, string $category, string $month): ?Budget;

    public function save(Budget $budget): Budget;

    public function delete(int $id): void;
}
