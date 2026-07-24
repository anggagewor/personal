<?php

namespace Modules\Trash\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Note\Infrastructure\Models\NoteModel;
use Modules\Task\Infrastructure\Models\TaskModel;
use Modules\Trash\Domain\Contracts\TrashRepositoryInterface;
use Modules\Trash\Domain\Entities\TrashItem;

class EloquentTrashRepository implements TrashRepositoryInterface
{
    public function getAll(int $userId): array
    {
        $items = [];
        $thirtyDaysAgo = now()->subDays(30);

        $trashedNotes = NoteModel::onlyTrashed()
            ->where('user_id', $userId)
            ->where('deleted_at', '>=', $thirtyDaysAgo)
            ->get();

        foreach ($trashedNotes as $note) {
            $items[] = new TrashItem(
                id: $note->id,
                type: 'note',
                title: $note->title,
                deletedAt: $note->deleted_at ? new DateTimeImmutable($note->deleted_at->toDateTimeString()) : null,
            );
        }

        $trashedTasks = TaskModel::onlyTrashed()
            ->where('user_id', $userId)
            ->where('deleted_at', '>=', $thirtyDaysAgo)
            ->get();

        foreach ($trashedTasks as $task) {
            $items[] = new TrashItem(
                id: $task->id,
                type: 'task',
                title: $task->title,
                deletedAt: $task->deleted_at ? new DateTimeImmutable($task->deleted_at->toDateTimeString()) : null,
            );
        }

        usort($items, fn ($a, $b) => $b->deletedAt <=> $a->deletedAt);

        return $items;
    }

    public function restore(string $type, int $id, int $userId): bool
    {
        $model = $this->getModel($type);
        $count = $model::onlyTrashed()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->restore();

        return $count > 0;
    }

    public function forceDelete(string $type, int $id, int $userId): bool
    {
        $model = $this->getModel($type);
        $item = $model::onlyTrashed()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$item) {
            return false;
        }

        $item->forceDelete();
        return true;
    }

    private function getModel(string $type): string
    {
        return match ($type) {
            'note' => NoteModel::class,
            'task' => TaskModel::class,
            default => throw new \InvalidArgumentException("Unknown trash type: {$type}"),
        };
    }
}
