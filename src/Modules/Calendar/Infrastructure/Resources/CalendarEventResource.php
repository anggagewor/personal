<?php

namespace Modules\Calendar\Infrastructure\Resources;

use Modules\Calendar\Domain\Entities\CalendarEvent;

class CalendarEventResource
{
    public static function toArray(CalendarEvent $event): array
    {
        return [
            'id' => $event->id,
            'user_id' => $event->userId,
            'title' => $event->title,
            'description' => $event->description,
            'start_at' => $event->startDate?->format('Y-m-d\TH:i:s.000000\Z'),
            'end_at' => $event->endDate?->format('Y-m-d\TH:i:s.000000\Z'),
            'color' => $event->color,
            'all_day' => $event->isAllDay,
            'created_at' => $event->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'updated_at' => $event->updatedAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $events): array
    {
        return array_map(fn (CalendarEvent $event) => self::toArray($event), $events);
    }
}
