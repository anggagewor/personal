<?php

namespace Modules\Scratchpad\Infrastructure\Resources;

use Modules\Scratchpad\Domain\Entities\Scratchpad;

class ScratchpadResource
{
    public static function toArray(Scratchpad $scratchpad): array
    {
        return [
            'id' => $scratchpad->id,
            'user_id' => $scratchpad->userId,
            'content' => $scratchpad->content,
            'color' => $scratchpad->color,
            'position' => $scratchpad->position,
            'created_at' => $scratchpad->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'updated_at' => $scratchpad->updatedAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $scratchpads): array
    {
        return array_map(fn (Scratchpad $scratchpad) => self::toArray($scratchpad), $scratchpads);
    }
}
