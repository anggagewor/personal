<?php

namespace Modules\Calendar\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Calendar\Domain\Contracts\CalendarEventRepositoryInterface;
use Modules\Calendar\Domain\Entities\CalendarEvent;
use Modules\Calendar\Infrastructure\Models\CalendarEventModel;

class EloquentCalendarEventRepository implements CalendarEventRepositoryInterface
{
    public function findById(int $id): ?CalendarEvent
    {
        $model = CalendarEventModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUserAndDateRange(int $userId, string $startDate, string $endDate): array
    {
        $models = CalendarEventModel::where('user_id', $userId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_at', [$startDate, $endDate])
                    ->orWhereBetween('end_at', [$startDate, $endDate]);
            })
            ->orderBy('start_at')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->toArray();
    }

    public function save(CalendarEvent $event): CalendarEvent
    {
        $model = CalendarEventModel::updateOrCreate(
            ['id' => $event->id],
            [
                'user_id' => $event->userId,
                'title' => $event->title,
                'description' => $event->description,
                'start_at' => $event->startDate?->format('Y-m-d H:i:s'),
                'end_at' => $event->endDate?->format('Y-m-d H:i:s'),
                'color' => $event->color,
                'all_day' => $event->isAllDay,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        CalendarEventModel::where('id', $id)->delete();
    }

    private function toEntity(CalendarEventModel $model): CalendarEvent
    {
        return new CalendarEvent(
            id: $model->id,
            userId: $model->user_id,
            title: $model->title,
            description: $model->description,
            startDate: $model->start_at ? new DateTimeImmutable($model->start_at->toDateTimeString()) : null,
            endDate: $model->end_at ? new DateTimeImmutable($model->end_at->toDateTimeString()) : null,
            color: $model->color,
            isAllDay: (bool) $model->all_day,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
