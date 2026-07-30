<?php

namespace Modules\Market\Infrastructure\Repositories;

use Modules\Market\Domain\Contracts\PriceHistoryRepositoryInterface;
use Modules\Market\Domain\Entities\PriceSnapshot;
use Modules\Market\Infrastructure\Models\PriceHistoryModel;

class EloquentPriceHistoryRepository implements PriceHistoryRepositoryInterface
{
    public function save(PriceSnapshot $snapshot): PriceSnapshot
    {
        $model = PriceHistoryModel::create([
            'user_id' => $snapshot->userId,
            'symbol' => $snapshot->symbol,
            'price' => $snapshot->price,
            'change' => $snapshot->change,
            'change_percent' => $snapshot->changePercent,
            'previous_close' => $snapshot->previousClose,
            'fetched_at' => $snapshot->fetchedAt ?? now(),
        ]);

        return $this->toEntity($model);
    }

    public function saveBatch(array $snapshots): void
    {
        $rows = array_map(fn(PriceSnapshot $s) => [
            'user_id' => $s->userId,
            'symbol' => $s->symbol,
            'price' => $s->price,
            'change' => $s->change,
            'change_percent' => $s->changePercent,
            'previous_close' => $s->previousClose,
            'fetched_at' => $s->fetchedAt?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
            'created_at' => now(),
            'updated_at' => now(),
        ], $snapshots);

        PriceHistoryModel::insert($rows);
    }

    public function getLatestPrices(int $userId): array
    {
        $rows = PriceHistoryModel::where('user_id', $userId)
            ->whereIn('id', function ($query) use ($userId) {
                $query->selectRaw('MAX(id)')
                    ->from('market_price_history')
                    ->where('user_id', $userId)
                    ->groupBy('symbol');
            })
            ->get();

        $result = [];
        foreach ($rows as $model) {
            $result[$model->symbol] = $this->toEntity($model);
        }

        return $result;
    }

    public function getHistory(int $userId, string $symbol, int $limit = 50): array
    {
        return PriceHistoryModel::where('user_id', $userId)
            ->where('symbol', $symbol)
            ->orderByDesc('fetched_at')
            ->limit($limit)
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->reverse()
            ->values()
            ->all();
    }

    public function getHistoryByRange(int $userId, string $symbol, string $from, string $to): array
    {
        return PriceHistoryModel::where('user_id', $userId)
            ->where('symbol', $symbol)
            ->whereBetween('fetched_at', [$from, $to])
            ->orderBy('fetched_at')
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->values()
            ->all();
    }

    public function getHistoryAllSymbols(int $userId): array
    {
        return PriceHistoryModel::where('user_id', $userId)
            ->orderBy('fetched_at')
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->values()
            ->all();
    }

    public function getHistoryAllSymbolsByRange(int $userId, string $from, string $to): array
    {
        return PriceHistoryModel::where('user_id', $userId)
            ->whereBetween('fetched_at', [$from, $to])
            ->orderBy('fetched_at')
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->values()
            ->all();
    }

    public function getSparklines(int $userId, int $points = 20): array
    {
        // Get all symbols for user
        $symbols = PriceHistoryModel::where('user_id', $userId)
            ->distinct()
            ->pluck('symbol')
            ->all();

        $result = [];

        foreach ($symbols as $symbol) {
            $prices = PriceHistoryModel::where('user_id', $userId)
                ->where('symbol', $symbol)
                ->orderByDesc('fetched_at')
                ->limit($points)
                ->pluck('price')
                ->reverse()
                ->values()
                ->all();

            $result[$symbol] = array_map(fn($p) => (float) $p, $prices);
        }

        return $result;
    }

    public function pruneOlderThan(int $days = 30): int
    {
        return PriceHistoryModel::where('fetched_at', '<', now()->subDays($days))->delete();
    }

    public function getOhlcHistory(int $userId, string $symbol, string $from, string $to, string $interval = '1d'): array
    {
        // Fetch raw tick data and aggregate to OHLC in PHP (MariaDB-friendly)
        $rows = PriceHistoryModel::where('user_id', $userId)
            ->where('symbol', $symbol)
            ->whereBetween('fetched_at', [$from, $to])
            ->orderBy('fetched_at')
            ->get(['price', 'fetched_at']);

        if ($rows->isEmpty()) {
            return [];
        }

        // Group by period
        $grouped = [];
        foreach ($rows as $row) {
            $period = $this->getPeriodKey($row->fetched_at, $interval);
            $grouped[$period][] = (float) $row->price;
        }

        // Build OHLC candles
        $candles = [];
        foreach ($grouped as $period => $prices) {
            $candles[] = [
                'time' => $period,
                'open' => $prices[0],
                'high' => max($prices),
                'low' => min($prices),
                'close' => end($prices),
            ];
        }

        return $candles;
    }

    private function getPeriodKey(\Illuminate\Support\Carbon $date, string $interval): string
    {
        return match ($interval) {
            '1h' => $date->format('Y-m-d H:00:00'),
            '4h' => $date->format('Y-m-d') . ' ' . str_pad((string) (intdiv((int) $date->format('H'), 4) * 4), 2, '0', STR_PAD_LEFT) . ':00:00',
            default => $date->format('Y-m-d'),
        };
    }

    private function toEntity(PriceHistoryModel $model): PriceSnapshot
    {
        return new PriceSnapshot(
            id: $model->id,
            userId: $model->user_id,
            symbol: $model->symbol,
            price: $model->price,
            change: $model->change,
            changePercent: $model->change_percent,
            previousClose: $model->previous_close,
            fetchedAt: $model->fetched_at ? new \DateTimeImmutable($model->fetched_at->toDateTimeString()) : null,
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
