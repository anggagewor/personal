<?php

namespace Modules\Gold\Application\Actions;

use Modules\Gold\Domain\Contracts\GoldPriceRepositoryInterface;

class ExportGoldPricesAction
{
    public function __construct(
        private GoldPriceRepositoryInterface $repository,
    ) {}

    /**
     * Export gold prices as array of rows.
     *
     * @return array<int, array{date: string, price: int, change: int, change_percent: float}>
     */
    public function execute(?string $from = null, ?string $to = null): array
    {
        if ($from && $to) {
            $prices = $this->repository->getRange($from, $to);
        } else {
            $prices = $this->repository->getHistory(99999);
        }

        return array_map(fn($p) => [
            'date' => $p->date,
            'price' => $p->price,
            'change' => $p->change,
            'change_percent' => $p->changePercent,
        ], $prices);
    }
}
