<?php

namespace Modules\Pos\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Pos\Application\DTO\CategoryData;
use Modules\Pos\Domain\Contracts\CategoryRepositoryInterface;
use Modules\Pos\Domain\Entities\Category;
use Modules\Pos\Infrastructure\Models\CategoryModel;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findByOutlet(int $outletId): array
    {
        return CategoryModel::where('outlet_id', $outletId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (CategoryModel $model) => $this->toEntity($model))
            ->all();
    }

    public function findById(int $id): ?Category
    {
        $model = CategoryModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function create(int $outletId, CategoryData $data): Category
    {
        $sortOrder = $data->sortOrder ?? CategoryModel::where('outlet_id', $outletId)->max('sort_order') + 1;

        $model = CategoryModel::create([
            'outlet_id' => $outletId,
            'parent_id' => $data->parentId,
            'name' => $data->name,
            'icon' => $data->icon,
            'sort_order' => $sortOrder,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function update(int $id, CategoryData $data): Category
    {
        $model = CategoryModel::findOrFail($id);

        $model->update([
            'parent_id' => $data->parentId,
            'name' => $data->name,
            'icon' => $data->icon,
            'sort_order' => $data->sortOrder ?? $model->sort_order,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        CategoryModel::where('id', $id)->delete();
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            CategoryModel::where('id', $id)->update(['sort_order' => $index]);
        }
    }

    public function existsByName(int $outletId, string $name, ?int $excludeId = null): bool
    {
        $query = CategoryModel::where('outlet_id', $outletId)
            ->where('name', $name);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function toEntity(CategoryModel $model): Category
    {
        return new Category(
            id: $model->id,
            outletId: $model->outlet_id,
            parentId: $model->parent_id,
            name: $model->name,
            icon: $model->icon,
            sortOrder: $model->sort_order,
            createdAt: $model->created_at
                ? new DateTimeImmutable($model->created_at->toDateTimeString())
                : null,
            updatedAt: $model->updated_at
                ? new DateTimeImmutable($model->updated_at->toDateTimeString())
                : null,
        );
    }
}
