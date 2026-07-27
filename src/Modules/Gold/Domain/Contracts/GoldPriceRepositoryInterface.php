<?php

namespace Modules\Gold\Domain\Contracts;

use Modules\Gold\Domain\Entities\GoldPrice;

interface GoldPriceRepositoryInterface
{
    public function getLatest(): ?GoldPrice;

    public function getByDate(string $date): ?GoldPrice;

    /** @return GoldPrice[] */
    public function getHistory(int $limit = 365): array;

    /** @return GoldPrice[] */
    public function getRange(string $from, string $to): array;

    /** @return float[] Last N prices for sparkline */
    public function getSparkline(int $points = 30): array;

    public function save(GoldPrice $price): GoldPrice;

    /** @param GoldPrice[] $prices */
    public function saveBatch(array $prices): void;

    public function count(): int;
}
