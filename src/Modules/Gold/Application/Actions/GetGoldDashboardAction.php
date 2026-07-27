<?php

namespace Modules\Gold\Application\Actions;

use Modules\Gold\Domain\Contracts\GoldPriceRepositoryInterface;

class GetGoldDashboardAction
{
    public function __construct(
        private GoldPriceRepositoryInterface $repository,
    ) {}

    /**
     * Get dashboard data: latest price, change, sparkline.
     */
    public function execute(): array
    {
        $latest = $this->repository->getLatest();

        if (!$latest) {
            return [
                'latest' => null,
                'sparkline' => [],
                'stats' => null,
            ];
        }

        $sparkline = $this->repository->getSparkline(30);

        // Calculate stats from last 30 days
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $history = $this->repository->getRange($thirtyDaysAgo, $latest->date);
        $prices = array_map(fn($p) => $p->price, $history);

        $stats = null;
        if (count($prices) > 1) {
            $firstPrice = $prices[0];
            $monthChange = $latest->price - $firstPrice;
            $monthChangePercent = $firstPrice > 0 ? ($monthChange / $firstPrice) * 100 : 0;
            $stats = [
                'high_30d' => max($prices),
                'low_30d' => min($prices),
                'change_30d' => $monthChange,
                'change_percent_30d' => round($monthChangePercent, 2),
            ];
        }

        return [
            'latest' => [
                'date' => $latest->date,
                'price' => $latest->price,
                'change' => $latest->change,
                'change_percent' => $latest->changePercent,
            ],
            'sparkline' => $sparkline,
            'stats' => $stats,
        ];
    }
}
