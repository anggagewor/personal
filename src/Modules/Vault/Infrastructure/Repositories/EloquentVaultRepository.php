<?php

namespace Modules\Vault\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Vault\Domain\Contracts\VaultRepositoryInterface;
use Modules\Vault\Domain\Entities\VaultEntry;
use Modules\Vault\Infrastructure\Models\VaultModel;

class EloquentVaultRepository implements VaultRepositoryInterface
{
    public function findById(int $id): ?VaultEntry
    {
        $model = VaultModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUser(int $userId, ?string $search = null, ?string $category = null): array
    {
        $query = VaultModel::where('user_id', $userId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        $models = $query->orderBy('name')->get();

        return $models->map(fn ($model) => $this->toEntity($model))->toArray();
    }

    public function save(VaultEntry $entry): VaultEntry
    {
        $model = VaultModel::updateOrCreate(
            ['id' => $entry->id],
            [
                'user_id' => $entry->userId,
                'name' => $entry->name,
                'username' => $entry->username,
                'encrypted_password' => $entry->encryptedPassword,
                'url' => $entry->url,
                'notes' => $entry->notes,
                'category' => $entry->category,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        VaultModel::where('id', $id)->delete();
    }

    public function getCategories(int $userId): array
    {
        return VaultModel::where('user_id', $userId)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
    }

    private function toEntity(VaultModel $model): VaultEntry
    {
        return new VaultEntry(
            id: $model->id,
            userId: $model->user_id,
            name: $model->name,
            username: $model->username,
            encryptedPassword: $model->encrypted_password ?? '',
            url: $model->url,
            notes: $model->notes,
            category: $model->category,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
