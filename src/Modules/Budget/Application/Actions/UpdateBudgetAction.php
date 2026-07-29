<?php

namespace Modules\Budget\Application\Actions;

use Modules\Budget\Application\DTO\BudgetData;
use Modules\Budget\Domain\Contracts\BudgetRepositoryInterface;
use Modules\Budget\Domain\Entities\Budget;

class UpdateBudgetAction
{
    public function __construct(
        private BudgetRepositoryInterface $repository,
    ) {}

    public function execute(int $budgetId, BudgetData $data): Budget
    {
        $existing = $this->repository->findById($budgetId);

        $budget = new Budget(
            id: $budgetId,
            userId: $existing->userId,
            category: $data->category,
            amount: $data->amount,
            month: $data->month,
        );

        return $this->repository->save($budget);
    }
}
