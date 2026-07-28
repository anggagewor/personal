<?php

namespace Modules\Gold\Application\Actions;

use Modules\Gold\Domain\Contracts\GoldPriceRepositoryInterface;
use Modules\Gold\Domain\Entities\GoldPrice;

class ImportGoldPricesAction
{
    public function __construct(
        private GoldPriceRepositoryInterface $repository,
    ) {}

    /**
     * Import gold prices from parsed rows.
     *
     * @param array<int, array{date: string, price: int, change?: int, change_percent?: float}> $rows
     * @return int Number of imported rows
     */
    public function execute(array $rows): int
    {
        $entities = [];

        foreach ($rows as $row) {
            $date = trim($row['date'] ?? '');
            $price = (int) ($row['price'] ?? 0);

            if (! $date || $price <= 0) {
                continue;
            }

            $entities[] = new GoldPrice(
                id: null,
                date: $date,
                price: $price,
                change: (int) ($row['change'] ?? 0),
                changePercent: (float) ($row['change_percent'] ?? 0),
            );
        }

        if (empty($entities)) {
            return 0;
        }

        $this->repository->saveBatch($entities);

        return count($entities);
    }
}
