<?php

namespace Modules\Market\Application\Actions;

use DateTimeImmutable;
use Modules\Market\Domain\Contracts\PriceHistoryRepositoryInterface;
use Modules\Market\Domain\Entities\PriceSnapshot;

class ImportMarketHistoryAction
{
    public function __construct(
        private PriceHistoryRepositoryInterface $repository,
    ) {}

    /**
     * Import market price history from parsed rows.
     *
     * @param array<int, array{symbol: string, price: float, change?: float, change_percent?: float, fetched_at?: string}> $rows
     * @return int Number of imported rows
     */
    public function execute(int $userId, array $rows): int
    {
        $entities = [];

        foreach ($rows as $row) {
            $symbol = trim($row['symbol'] ?? '');
            $price = (float) ($row['price'] ?? 0);

            if (! $symbol || $price <= 0) {
                continue;
            }

            $fetchedAt = ! empty($row['fetched_at'])
                ? new DateTimeImmutable($row['fetched_at'])
                : new DateTimeImmutable();

            $entities[] = new PriceSnapshot(
                id: null,
                userId: $userId,
                symbol: strtoupper($symbol),
                price: $price,
                change: (float) ($row['change'] ?? 0),
                changePercent: (float) ($row['change_percent'] ?? 0),
                fetchedAt: $fetchedAt,
            );
        }

        if (empty($entities)) {
            return 0;
        }

        $this->repository->saveBatch($entities);

        return count($entities);
    }
}
