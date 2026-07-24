<?php

namespace Modules\Journal\Infrastructure\Resources;

use Modules\Journal\Domain\Entities\Journal;

class JournalResource
{
    public static function toArray(Journal $journal): array
    {
        return [
            'id' => $journal->id,
            'user_id' => $journal->userId,
            'content' => $journal->content,
            'mood' => $journal->mood?->value,
            'date' => $journal->date?->format('Y-m-d'),
            'created_at' => $journal->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $journals): array
    {
        return array_map(fn (Journal $journal) => self::toArray($journal), $journals);
    }
}
