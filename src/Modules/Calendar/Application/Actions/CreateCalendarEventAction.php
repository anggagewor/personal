<?php

namespace Modules\Calendar\Application\Actions;

use DateTimeImmutable;
use Modules\Calendar\Application\DTO\CalendarEventData;
use Modules\Calendar\Domain\Contracts\CalendarEventRepositoryInterface;
use Modules\Calendar\Domain\Entities\CalendarEvent;

class CreateCalendarEventAction
{
    public function __construct(
        private CalendarEventRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, CalendarEventData $data): CalendarEvent
    {
        $event = new CalendarEvent(
            id: null,
            userId: $userId,
            title: $data->title,
            description: $data->description,
            startDate: $data->startDate ? new DateTimeImmutable($data->startDate) : null,
            endDate: $data->endDate ? new DateTimeImmutable($data->endDate) : null,
            color: $data->color,
            isAllDay: $data->isAllDay,
        );

        return $this->repository->save($event);
    }
}
