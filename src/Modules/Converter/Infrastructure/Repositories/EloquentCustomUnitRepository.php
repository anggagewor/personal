<?php

namespace Modules\Converter\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Converter\Domain\Contracts\CustomUnitRepositoryInterface;
use Modules\Converter\Domain\Entities\CustomUnit;
use Modules\Converter\Infrastructure\Models\CustomUnitModel;

class EloquentCustomUnitRepository implements CustomUnitRepositoryInterface
{
    public function findById(int $id): ?CustomUnit
    {
        $model = CustomUnitModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByCategory(int $categoryId): array
    {
        $models = CustomUnitModel::where('category_id', $categoryId)
            ->orderByDesc('is_base')
            ->orderBy('name')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->all();
    }

    public function save(CustomUnit $unit): CustomUnit
    {
        $model = CustomUnitModel::updateOrCreate(
            ['id' => $unit->id],
            [
                'category_id' => $unit->categoryId,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'to_base' => $unit->toBase,
                'is_base' => $unit->isBase,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        CustomUnitModel::where('id', $id)->delete();
    }

    private function toEntity(CustomUnitModel $model): CustomUnit
    {
        return new CustomUnit(
            id: $model->id,
            categoryId: $model->category_id,
            name: $model->name,
            symbol: $model->symbol,
            toBase: $model->to_base,
            isBase: $model->is_base,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
