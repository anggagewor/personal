<?php

namespace Modules\Quote\Infrastructure\Repositories;

use Modules\Quote\Domain\Contracts\QuoteRepositoryInterface;
use Modules\Quote\Domain\Entities\Quote;
use Modules\Quote\Infrastructure\Models\QuoteModel;

class EloquentQuoteRepository implements QuoteRepositoryInterface
{
    public function getToday(): ?Quote
    {
        $dayOfYear = now()->dayOfYear;
        $totalQuotes = QuoteModel::count();

        if ($totalQuotes === 0) {
            return null;
        }

        $index = $dayOfYear % $totalQuotes;
        $model = QuoteModel::skip($index)->first();

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function paginate(int $page = 1, int $perPage = 10, ?string $search = null): array
    {
        $query = QuoteModel::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $query->orderBy('id', 'desc');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(fn (QuoteModel $model) => $this->toEntity($model))->all();

        return [
            'items' => $items,
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    public function save(Quote $quote): Quote
    {
        $model = QuoteModel::updateOrCreate(
            ['id' => $quote->id],
            [
                'content' => $quote->content,
                'author' => $quote->author,
            ]
        );

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        QuoteModel::where('id', $id)->delete();
    }

    private function toEntity(QuoteModel $model): Quote
    {
        return new Quote(
            id: $model->id,
            content: $model->content,
            author: $model->author,
        );
    }
}
