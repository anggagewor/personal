<?php

namespace Modules\Market\Domain\Contracts;

use Modules\Market\Domain\Entities\PriceSnapshot;

interface PriceHistoryRepositoryInterface
{
    /**
     * Save a price snapshot.
     */
    public function save(PriceSnapshot $snapshot): PriceSnapshot;

    /**
     * Save multiple snapshots at once.
     *
     * @param PriceSnapshot[] $snapshots
     */
    public function saveBatch(array $snapshots): void;

    /**
     * Get the latest price for each symbol for a user.
     *
     * @return array<string, PriceSnapshot> Keyed by symbol
     */
    public function getLatestPrices(int $userId): array;

    /**
     * Get price history for a symbol (last N entries).
     *
     * @return PriceSnapshot[]
     */
    public function getHistory(int $userId, string $symbol, int $limit = 50): array;

    /**
     * Get mini sparkline data for all symbols (last N price points each).
     *
     * @return array<string, float[]> Keyed by symbol, value is array of prices (oldest first)
     */
    public function getSparklines(int $userId, int $points = 20): array;

    /**
     * Clean up old history (keep last N days).
     */
    public function pruneOlderThan(int $days = 30): int;
}
