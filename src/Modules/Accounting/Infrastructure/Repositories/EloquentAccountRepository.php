<?php

namespace Modules\Accounting\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\Enums\AccountType;
use Modules\Accounting\Domain\Enums\NormalBalance;
use Modules\Accounting\Infrastructure\Models\AccountModel;
use Modules\Accounting\Infrastructure\Models\JournalLineModel;

class EloquentAccountRepository implements AccountRepositoryInterface
{
    public function findById(int $id): ?Account
    {
        $model = AccountModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByCode(int $userId, string $code): ?Account
    {
        $model = AccountModel::where('user_id', $userId)
            ->where('code', $code)
            ->first();

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUser(int $userId): array
    {
        $models = AccountModel::where('user_id', $userId)
            ->orderBy('code')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->all();
    }

    public function findByUserGroupedByType(int $userId): array
    {
        $models = AccountModel::where('user_id', $userId)
            ->orderBy('code')
            ->get();

        $grouped = [];

        foreach ($models as $model) {
            $type = $model->type;
            $grouped[$type][] = $this->toEntity($model);
        }

        return $grouped;
    }

    public function getDepth(int $accountId): int
    {
        $model = AccountModel::find($accountId);

        return $model ? $model->depth : 0;
    }

    public function hasJournalLines(int $accountId): bool
    {
        return JournalLineModel::where('account_id', $accountId)->exists();
    }

    public function save(Account $account): Account
    {
        $model = AccountModel::updateOrCreate(
            ['id' => $account->id],
            [
                'user_id' => $account->userId,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type->value,
                'normal_balance' => $account->normalBalance->value,
                'parent_id' => $account->parentId,
                'depth' => $account->depth,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        AccountModel::where('id', $id)->delete();
    }

    public function countByUser(int $userId): int
    {
        return AccountModel::where('user_id', $userId)->count();
    }

    public function deleteAllByUser(int $userId): void
    {
        AccountModel::where('user_id', $userId)->delete();
    }

    private function toEntity(AccountModel $model): Account
    {
        return new Account(
            id: $model->id,
            userId: $model->user_id,
            code: $model->code,
            name: $model->name,
            type: AccountType::from($model->type),
            normalBalance: NormalBalance::from($model->normal_balance),
            parentId: $model->parent_id,
            depth: $model->depth,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
