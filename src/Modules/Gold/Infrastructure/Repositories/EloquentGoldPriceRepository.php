<?php

namespace Modules\Gold\Infrastructure\Repositories;

use Modules\Gold\Domain\Contracts\GoldPriceRepositoryInterface;
use Modules\Gold\Domain\Entities\GoldPrice;
use Modules\Gold\Infrastructure\Models\GoldPriceModel;

class EloquentGoldPriceRepository implements GoldPriceRepositoryInterface
{
    public function getLatest(): ?GoldPrice
    {
        $model = GoldPriceModel::orderByDesc('date')->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function getByDate(string $date): ?GoldPrice
    {
        $model = GoldPriceModel::where('date', $date)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function getHistory(int $limit = 365): array
    {
        return GoldPriceModel::orderByDesc('date')
            ->limit($limit)
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->reverse()
            ->values()
            ->all();
    }

    public function getRange(string $from, string $to): array
    {
        return GoldPriceModel::whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->all();
    }

    public function getSparkline(int $points = 30): array
    {
        return GoldPriceModel::orderByDesc('date')
            ->limit($points)
            ->pluck('price')
            ->reverse()
            ->values()
            ->map(fn($p) => (float) $p)
            ->all();
    }

    public function save(GoldPrice $price): GoldPrice
    {
        $model = GoldPriceModel::updateOrCreate(
            ['date' => $price->date],
            [
                'price' => $price->price,
                'change' => $price->change,
                'change_percent' => $price->changePercent,
            ]
        );

        return $this->toEntity($model);
    }

    public function saveBatch(array $prices): void
    {
        $rows = array_map(fn(GoldPrice $p) => [
            'date' => $p->date,
            'price' => $p->price,
            'change' => $p->change,
            'change_percent' => $p->changePercent,
            'created_at' => now(),
            'updated_at' => now(),
        ], $prices);

        // Chunk insert to avoid memory issues with large datasets
        foreach (array_chunk($rows, 500) as $chunk) {
            GoldPriceModel::upsert($chunk, ['date'], ['price', 'change', 'change_percent', 'updated_at']);
        }
    }

    public function count(): int
    {
        return GoldPriceModel::count();
    }

    private function toEntity(GoldPriceModel $model): GoldPrice
    {
        return new GoldPrice(
            id: $model->id,
            date: $model->date->format('Y-m-d'),
            price: $model->price,
            change: $model->change,
            changePercent: $model->change_percent,
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
