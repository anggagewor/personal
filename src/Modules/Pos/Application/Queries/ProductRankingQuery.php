<?php

namespace Modules\Pos\Application\Queries;

use Modules\Pos\Domain\Contracts\ReportRepositoryInterface;

class ProductRankingQuery
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepo,
    ) {}

    public function execute(int $outletId, string $from, string $to, int $limit = 10): array
    {
        return $this->reportRepo->getProductRanking($outletId, $from, $to, $limit);
    }
}
