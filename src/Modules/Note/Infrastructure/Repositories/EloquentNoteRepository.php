<?php

namespace Modules\Note\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Note\Domain\Contracts\NoteRepositoryInterface;
use Modules\Note\Domain\Entities\Note;
use Modules\Note\Infrastructure\Models\NoteModel;

class EloquentNoteRepository implements NoteRepositoryInterface
{
    public function findById(int $id): ?Note
    {
        $model = NoteModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUserPaginated(int $userId, ?string $search = null, int $perPage = 15): array
    {
        $query = NoteModel::where('user_id', $userId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate($perPage);

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

    public function save(Note $note): Note
    {
        $model = NoteModel::updateOrCreate(
            ['id' => $note->id],
            [
                'user_id' => $note->userId,
                'title' => $note->title,
                'content' => $note->content,
                'is_pinned' => $note->isPinned,
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        NoteModel::where('id', $id)->delete();
    }

    private function toEntity(NoteModel $model): Note
    {
        return new Note(
            id: $model->id,
            userId: $model->user_id,
            title: $model->title,
            content: $model->content,
            isPinned: (bool) $model->is_pinned,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
            deletedAt: $model->deleted_at ? new DateTimeImmutable($model->deleted_at->toDateTimeString()) : null,
        );
    }
}
