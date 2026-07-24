<?php

namespace Modules\Calendar\Application\Actions;

use DateTimeImmutable;
use Modules\Calendar\Application\DTO\CalendarEventData;
use Modules\Calendar\Domain\Contracts\CalendarEventRepositoryInterface;
use Modules\Calendar\Domain\Entities\CalendarEvent;

class UpdateCalendarEventAction
{
    public function __construct(
        private CalendarEventRepositoryInterface $repository,
    ) {}

    public function execute(int $eventId, CalendarEventData $data): CalendarEvent
    {
        $event = $this->repository->findById($eventId);

        $event->title = $data->title;
        $event->description = $data->description;
        $event->startDate = $data->startDate ? new DateTimeImmutable($data->startDate) : null;
        $event->endDate = $data->endDate ? new DateTimeImmutable($data->endDate) : null;
        $event->color = $data->color;
        $event->isAllDay = $data->isAllDay;

        return $this->repository->save($event);
    }
}
