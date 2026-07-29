<?php

namespace Modules\Budget\Application\Actions;

use Modules\Budget\Application\DTO\BudgetData;
use Modules\Budget\Domain\Contracts\BudgetRepositoryInterface;
use Modules\Budget\Domain\Entities\Budget;

class CreateBudgetAction
{
    public function __construct(
        private BudgetRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, BudgetData $data): Budget
    {
        // Check if budget already exists for this user/category/month
        $existing = $this->repository->findByUserCategoryMonth($userId, $data->category, $data->month);

        if ($existing) {
            // Update existing budget
            $budget = new Budget(
                id: $existing->id,
                userId: $userId,
                category: $data->category,
                amount: $data->amount,
                month: $data->month,
            );
        } else {
            $budget = new Budget(
                id: null,
                userId: $userId,
                category: $data->category,
                amount: $data->amount,
                month: $data->month,
            );
        }

        return $this->repository->save($budget);
    }
}
