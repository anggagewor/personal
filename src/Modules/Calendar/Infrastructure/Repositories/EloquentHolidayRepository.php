<?php

namespace Modules\Calendar\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Calendar\Domain\Contracts\HolidayRepositoryInterface;
use Modules\Calendar\Domain\Entities\Holiday;
use Modules\Calendar\Infrastructure\Models\HolidayModel;

class EloquentHolidayRepository implements HolidayRepositoryInterface
{
    public function getByDateRange(string $startDate, string $endDate): array
    {
        $models = HolidayModel::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->toArray();
    }

    public function getNationalByDateRange(string $startDate, string $endDate): array
    {
        $models = HolidayModel::whereBetween('date', [$startDate, $endDate])
            ->where('is_national', true)
            ->orderBy('date')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->toArray();
    }

    private function toEntity(HolidayModel $model): Holiday
    {
        return new Holiday(
            id: $model->id,
            name: $model->name,
            date: new DateTimeImmutable($model->date->format('Y-m-d')),
            type: $model->type,
            isNational: (bool) $model->is_national,
        );
    }
}
