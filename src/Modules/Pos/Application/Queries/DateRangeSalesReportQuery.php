<?php

namespace Modules\Pos\Application\Queries;

use Modules\Pos\Domain\Contracts\ReportRepositoryInterface;

class DateRangeSalesReportQuery
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepo,
    ) {}

    public function execute(int $outletId, string $from, string $to): array
    {
        return $this->reportRepo->getDateRangeSummary($outletId, $from, $to);
    }
}
