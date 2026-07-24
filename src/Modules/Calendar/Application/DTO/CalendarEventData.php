<?php

namespace Modules\Calendar\Application\DTO;

class CalendarEventData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $color = null,
        public readonly bool $isAllDay = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            startDate: $data['start_at'] ?? null,
            endDate: $data['end_at'] ?? null,
            color: $data['color'] ?? null,
            isAllDay: $data['all_day'] ?? false,
        );
    }
}
