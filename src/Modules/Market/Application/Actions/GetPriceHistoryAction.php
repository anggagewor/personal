<?php

namespace Modules\Market\Application\Actions;

use Modules\Market\Domain\Contracts\PriceHistoryRepositoryInterface;
use Modules\Market\Domain\Entities\PriceSnapshot;

class GetPriceHistoryAction
{
    public function __construct(
        private PriceHistoryRepositoryInterface $repository,
    ) {}

    /**
     * Get price history for a specific symbol.
     *
     * @return PriceSnapshot[]
     */
    public function execute(int $userId, string $symbol, int $limit = 50): array
    {
        return $this->repository->getHistory($userId, $symbol, $limit);
    }
}
