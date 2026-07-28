<?php

namespace Modules\Gold\Application\Actions;

use Modules\Gold\Domain\Contracts\GoldPriceFetcherInterface;
use Modules\Gold\Domain\Contracts\GoldPriceRepositoryInterface;
use Modules\Gold\Domain\Entities\GoldPrice;

class FetchGoldDailyAction
{
    public function __construct(
        private GoldPriceFetcherInterface $fetcher,
        private GoldPriceRepositoryInterface $repository,
    ) {}

    /**
     * Fetch today's gold price, calculate change, and persist.
     *
     * @throws \RuntimeException when price cannot be fetched or parsed.
     */
    public function execute(): GoldPrice
    {
        $price = $this->fetcher->fetchCurrentPrice();

        if (! $price || $price <= 0) {
            throw new \RuntimeException('Could not extract valid price from API response.');
        }

        $today = today()->toDateString();

        $latest = $this->repository->getLatest();
        $prevPrice = $latest?->price ?? 0;

        $change = $prevPrice > 0 ? $price - $prevPrice : 0;
        $changePercent = $prevPrice > 0
            ? round(($change / $prevPrice) * 100, 4)
            : 0;

        $entity = new GoldPrice(
            id: null,
            date: $today,
            price: $price,
            change: $change,
            changePercent: $changePercent,
        );

        return $this->repository->save($entity);
    }
}
