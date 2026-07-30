<?php

namespace Modules\Converter\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Converter\Domain\Contracts\CustomCategoryRepositoryInterface;
use Modules\Converter\Domain\Entities\CustomCategory;
use Modules\Converter\Infrastructure\Models\CustomCategoryModel;

class EloquentCustomCategoryRepository implements CustomCategoryRepositoryInterface
{
    public function findById(int $id): ?CustomCategory
    {
        $model = CustomCategoryModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUser(int $userId): array
    {
        $models = CustomCategoryModel::where('user_id', $userId)
            ->with('units')
            ->orderBy('name')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->all();
    }

    public function save(CustomCategory $category): CustomCategory
    {
        $model = CustomCategoryModel::updateOrCreate(
            ['id' => $category->id],
            [
                'user_id' => $category->userId,
                'name' => $category->name,
                'description' => $category->description,
                'icon' => $category->icon,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        CustomCategoryModel::where('id', $id)->delete();
    }

    private function toEntity(CustomCategoryModel $model): CustomCategory
    {
        return new CustomCategory(
            id: $model->id,
            userId: $model->user_id,
            name: $model->name,
            description: $model->description,
            icon: $model->icon,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
