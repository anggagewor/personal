<?php

namespace Modules\Finance\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Finance\Domain\Contracts\FinanceRepositoryInterface;
use Modules\Finance\Domain\Entities\Finance;
use Modules\Finance\Domain\Enums\FinanceType;
use Modules\Finance\Infrastructure\Models\FinanceModel;

class EloquentFinanceRepository implements FinanceRepositoryInterface
{
    public function findById(int $id): ?Finance
    {
        $model = FinanceModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUserPaginated(int $userId, ?string $month = null, ?string $type = null, int $perPage = 15): array
    {
        $query = FinanceModel::where('user_id', $userId);

        if ($month) {
            $query->whereRaw("strftime('%Y-%m', date) = ?", [$month]);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $paginator = $query->orderByDesc('date')->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function save(Finance $finance): Finance
    {
        $model = FinanceModel::updateOrCreate(
            ['id' => $finance->id],
            [
                'user_id' => $finance->userId,
                'type' => $finance->type->value,
                'amount' => $finance->amount,
                'category' => $finance->category,
                'description' => $finance->description,
                'date' => $finance->date?->format('Y-m-d'),
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        FinanceModel::where('id', $id)->delete();
    }

    public function getSummary(int $userId, ?string $month = null): array
    {
        $query = FinanceModel::where('user_id', $userId);

        if ($month) {
            $query->whereRaw("strftime('%Y-%m', date) = ?", [$month]);
        }

        $income = (clone $query)->where('type', 'income')->sum('amount');
        $expense = (clone $query)->where('type', 'expense')->sum('amount');

        return [
            'income' => (float) $income,
            'expense' => (float) $expense,
            'balance' => (float) ($income - $expense),
        ];
    }

    private function toEntity(FinanceModel $model): Finance
    {
        return new Finance(
            id: $model->id,
            userId: $model->user_id,
            type: FinanceType::from($model->type),
            amount: (float) $model->amount,
            category: $model->category,
            description: $model->description,
            date: $model->date ? new DateTimeImmutable($model->date->format('Y-m-d')) : null,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
