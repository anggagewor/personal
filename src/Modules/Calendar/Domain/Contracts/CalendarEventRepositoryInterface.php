<?php

namespace Modules\Calendar\Domain\Contracts;

use Modules\Calendar\Domain\Entities\CalendarEvent;

interface CalendarEventRepositoryInterface
{
    public function findById(int $id): ?CalendarEvent;

    public function findByUserAndDateRange(int $userId, string $startDate, string $endDate): array;

    public function save(CalendarEvent $event): CalendarEvent;

    public function delete(int $id): void;
}
