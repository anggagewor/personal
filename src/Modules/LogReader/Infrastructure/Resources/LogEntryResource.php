<?php

namespace Modules\LogReader\Infrastructure\Resources;

use Modules\LogReader\Domain\Entities\LogEntry;

class LogEntryResource
{
    public static function toArray(LogEntry $entry): array
    {
        return [
            'datetime' => $entry->datetime->format('Y-m-d H:i:s'),
            'level' => $entry->level->value,
            'level_color' => $entry->level->color(),
            'environment' => $entry->environment,
            'message' => $entry->message,
            'stack_trace' => $entry->stackTrace,
            'context' => $entry->context,
            'has_stack_trace' => $entry->stackTrace !== '',
        ];
    }

    public static function collection(array $entries): array
    {
        return array_map(fn (LogEntry $entry) => self::toArray($entry), $entries);
    }
}
