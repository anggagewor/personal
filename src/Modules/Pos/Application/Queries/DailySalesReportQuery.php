<?php

namespace Modules\Pos\Application\Queries;

use Modules\Pos\Domain\Contracts\ReportRepositoryInterface;

class DailySalesReportQuery
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepo,
    ) {}

    public function execute(int $outletId, string $date): array
    {
        return $this->reportRepo->getDailySummary($outletId, $date);
    }
}
