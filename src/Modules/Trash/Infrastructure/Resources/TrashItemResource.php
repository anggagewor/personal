<?php

namespace Modules\Trash\Infrastructure\Resources;

use Modules\Trash\Domain\Entities\TrashItem;

class TrashItemResource
{
    public static function toArray(TrashItem $item): array
    {
        return [
            'id' => $item->id,
            'type' => $item->type,
            'title' => $item->title,
            'deleted_at' => $item->deletedAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $items): array
    {
        return array_map(fn (TrashItem $item) => self::toArray($item), $items);
    }
}
