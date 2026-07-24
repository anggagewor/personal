<?php

namespace Modules\Calendar\Application\Actions;

use Modules\Calendar\Domain\Contracts\CalendarEventRepositoryInterface;

class DeleteCalendarEventAction
{
    public function __construct(
        private CalendarEventRepositoryInterface $repository,
    ) {}

    public function execute(int $eventId): void
    {
        $this->repository->delete($eventId);
    }
}
