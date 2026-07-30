<?php

namespace Modules\Market\Application\Actions;

use Modules\Market\Domain\Contracts\PriceHistoryRepositoryInterface;

class GetOhlcHistoryAction
{
    public function __construct(
        private PriceHistoryRepositoryInterface $repository,
    ) {}

    /**
     * Get OHLC candlestick data for a specific symbol.
     *
     * @param string $interval One of: '1h', '4h', '1d'
     * @return array<int, array{time: string, open: float, high: float, low: float, close: float}>
     */
    public function execute(int $userId, string $symbol, string $from, string $to, string $interval = '1d'): array
    {
        $allowed = ['1h', '4h', '1d'];
        if (!in_array($interval, $allowed)) {
            $interval = '1d';
        }

        return $this->repository->getOhlcHistory($userId, $symbol, $from, $to, $interval);
    }
}
