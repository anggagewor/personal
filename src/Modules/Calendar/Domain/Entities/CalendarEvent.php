<?php

namespace Modules\Calendar\Domain\Entities;

use DateTimeImmutable;

class CalendarEvent
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $title,
        public ?string $description = null,
        public ?DateTimeImmutable $startDate = null,
        public ?DateTimeImmutable $endDate = null,
        public ?string $color = null,
        public bool $isAllDay = false,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
