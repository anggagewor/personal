<?php

namespace Modules\Budget\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Budget\Domain\Contracts\BudgetRepositoryInterface;
use Modules\Budget\Domain\Entities\Budget;
use Modules\Budget\Infrastructure\Models\BudgetModel;

class EloquentBudgetRepository implements BudgetRepositoryInterface
{
    public function findById(int $id): ?Budget
    {
        $model = BudgetModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUserAndMonth(int $userId, string $month): array
    {
        $models = BudgetModel::where('user_id', $userId)
            ->where('month', $month)
            ->orderBy('category')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->toArray();
    }

    public function findByUserCategoryMonth(int $userId, string $category, string $month): ?Budget
    {
        $model = BudgetModel::where('user_id', $userId)
            ->where('category', $category)
            ->where('month', $month)
            ->first();

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function save(Budget $budget): Budget
    {
        $model = BudgetModel::updateOrCreate(
            ['id' => $budget->id],
            [
                'user_id' => $budget->userId,
                'category' => $budget->category,
                'amount' => $budget->amount,
                'month' => $budget->month,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        BudgetModel::where('id', $id)->delete();
    }

    private function toEntity(BudgetModel $model): Budget
    {
        return new Budget(
            id: $model->id,
            userId: $model->user_id,
            category: $model->category,
            amount: (float) $model->amount,
            month: $model->month,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
