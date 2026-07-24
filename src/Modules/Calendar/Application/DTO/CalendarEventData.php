<?php

namespace Modules\Calendar\Application\DTO;

readonly class CalendarEventData
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?string $color = null,
        public bool $isAllDay = false,
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
