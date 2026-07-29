<?php

namespace Modules\Budget\Application\Queries;

use Modules\Budget\Domain\Contracts\BudgetRepositoryInterface;
use Modules\Finance\Infrastructure\Models\FinanceModel;

class GetBudgetSummaryQuery
{
    public function __construct(
        private BudgetRepositoryInterface $repository,
    ) {}

    /**
     * Get budget summary with actual spending for a given month.
     * Returns array of budgets with spent amounts and status.
     */
    public function execute(int $userId, string $month): array
    {
        $budgets = $this->repository->findByUserAndMonth($userId, $month);

        if (empty($budgets)) {
            return [];
        }

        // Get actual spending by category for this month
        [$year, $mon] = explode('-', $month);
        $spending = FinanceModel::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $mon)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $totalBudget = 0;
        $totalSpent = 0;
        $results = [];

        foreach ($budgets as $budget) {
            $spent = (float) ($spending[$budget->category] ?? 0);
            $totalBudget += $budget->amount;
            $totalSpent += $spent;

            $results[] = [
                'id' => $budget->id,
                'category' => $budget->category,
                'amount' => $budget->amount,
                'spent' => $spent,
                'remaining' => $budget->remainingAmount($spent),
                'percent_used' => round($budget->percentUsed($spent), 1),
                'is_exceeded' => $budget->isExceeded($spent),
                'is_near_limit' => $budget->isNearLimit($spent),
            ];
        }

        return [
            'budgets' => $results,
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'total_remaining' => max(0, $totalBudget - $totalSpent),
        ];
    }
}
