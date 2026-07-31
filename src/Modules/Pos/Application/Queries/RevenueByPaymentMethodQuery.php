<?php

namespace Modules\Pos\Application\Queries;

use Modules\Pos\Domain\Contracts\ReportRepositoryInterface;

class RevenueByPaymentMethodQuery
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepo,
    ) {}

    public function execute(int $outletId, string $from, string $to): array
    {
        return $this->reportRepo->getRevenueByPaymentMethod($outletId, $from, $to);
    }
}
