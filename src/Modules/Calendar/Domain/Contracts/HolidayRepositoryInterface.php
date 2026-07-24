<?php

namespace Modules\Calendar\Domain\Contracts;

use Modules\Calendar\Domain\Entities\Holiday;

interface HolidayRepositoryInterface
{
    /** @return Holiday[] */
    public function getByDateRange(string $startDate, string $endDate): array;

    /** @return Holiday[] */
    public function getNationalByDateRange(string $startDate, string $endDate): array;
}
