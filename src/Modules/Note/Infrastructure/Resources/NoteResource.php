<?php

namespace Modules\Note\Infrastructure\Resources;

use Modules\Note\Domain\Entities\Note;

class NoteResource
{
    public static function toArray(Note $note): array
    {
        return [
            'id' => $note->id,
            'user_id' => $note->userId,
            'title' => $note->title,
            'content' => $note->content,
            'is_pinned' => $note->isPinned,
            'created_at' => $note->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'updated_at' => $note->updatedAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $notes): array
    {
        return array_map(fn (Note $note) => self::toArray($note), $notes);
    }
}
