<?php

namespace Modules\Market\Application\Actions;

use Modules\Market\Domain\Contracts\PriceHistoryRepositoryInterface;

class ExportMarketHistoryAction
{
    public function __construct(
        private PriceHistoryRepositoryInterface $repository,
    ) {}

    /**
     * Export market price history as array of rows.
     *
     * @return array<int, array{symbol: string, price: float, change: float, change_percent: float, fetched_at: string}>
     */
    public function execute(int $userId, ?string $from = null, ?string $to = null): array
    {
        if ($from && $to) {
            $history = $this->repository->getHistoryAllSymbolsByRange($userId, $from, $to);
        } else {
            $history = $this->repository->getHistoryAllSymbols($userId);
        }

        return array_map(fn($s) => [
            'symbol' => $s->symbol,
            'price' => $s->price,
            'change' => $s->change,
            'change_percent' => $s->changePercent,
            'fetched_at' => $s->fetchedAt?->format('Y-m-d H:i:s') ?? '',
        ], $history);
    }
}
