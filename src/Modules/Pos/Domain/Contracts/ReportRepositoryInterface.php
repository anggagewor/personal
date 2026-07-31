<?php

namespace Modules\Pos\Domain\Contracts;

interface ReportRepositoryInterface
{
    public function getDailySummary(int $outletId, string $date): array;

    public function getDateRangeSummary(int $outletId, string $from, string $to): array;

    public function getProductRanking(int $outletId, string $from, string $to, int $limit): array;

    public function getRevenueByPaymentMethod(int $outletId, string $from, string $to): array;

    public function getDashboardStats(int $outletId): array;
}
