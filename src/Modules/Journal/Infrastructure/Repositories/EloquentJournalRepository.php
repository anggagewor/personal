<?php

namespace Modules\Journal\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Journal\Domain\Contracts\JournalRepositoryInterface;
use Modules\Journal\Domain\Entities\Journal;
use Modules\Journal\Domain\Enums\JournalMood;
use Modules\Journal\Infrastructure\Models\JournalModel;

class EloquentJournalRepository implements JournalRepositoryInterface
{
    public function findById(int $id): ?Journal
    {
        $model = JournalModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUserPaginated(int $userId, ?string $month = null, int $perPage = 15): array
    {
        $query = JournalModel::where('user_id', $userId);

        if ($month) {
            $query->whereRaw("strftime('%Y-%m', date) = ?", [$month]);
        }

        $paginator = $query->orderByDesc('date')->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function findByDate(int $userId, string $date): ?Journal
    {
        $model = JournalModel::where('user_id', $userId)
            ->whereDate('date', $date)
            ->first();

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function save(Journal $journal): Journal
    {
        // Upsert by user + date
        $dateString = $journal->date?->format('Y-m-d');

        // Use Carbon to ensure consistent date serialization for lookup
        $carbonDate = \Illuminate\Support\Carbon::parse($dateString)->startOfDay();

        $existing = JournalModel::where('user_id', $journal->userId)
            ->where('date', $carbonDate)
            ->first();

        if ($existing) {
            $existing->update([
                'content' => $journal->content,
                'mood' => $journal->mood?->value,
            ]);
            return $this->toEntity($existing->fresh());
        }

        $model = JournalModel::create([
            'user_id' => $journal->userId,
            'content' => $journal->content,
            'mood' => $journal->mood?->value,
            'date' => $carbonDate,
        ]);

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        JournalModel::where('id', $id)->delete();
    }

    public function findMoodsByUser(int $userId): array
    {
        $models = JournalModel::where('user_id', $userId)
            ->whereNotNull('mood')
            ->orderByDesc('date')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->toArray();
    }

    private function toEntity(JournalModel $model): Journal
    {
        return new Journal(
            id: $model->id,
            userId: $model->user_id,
            content: $model->content,
            mood: $model->mood ? JournalMood::from($model->mood) : null,
            date: $model->date ? new DateTimeImmutable($model->date->format('Y-m-d')) : null,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
