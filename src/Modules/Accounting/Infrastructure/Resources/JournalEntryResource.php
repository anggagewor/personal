<?php

namespace Modules\Accounting\Infrastructure\Resources;

use Modules\Accounting\Domain\Entities\JournalEntry;

class JournalEntryResource
{
    public static function toArray(JournalEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'entry_number' => $entry->entryNumber,
            'date' => $entry->date->format('Y-m-d'),
            'description' => $entry->description,
            'total_debit' => $entry->totalDebit(),
            'lines' => array_map(fn ($line) => [
                'id' => $line->id,
                'account_id' => $line->accountId,
                'debit' => $line->debit,
                'credit' => $line->credit,
            ], $entry->lines),
            'created_at' => $entry->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $entries): array
    {
        return array_map(fn (JournalEntry $entry) => self::toArray($entry), $entries);
    }
}
