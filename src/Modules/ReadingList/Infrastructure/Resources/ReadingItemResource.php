<?php

namespace Modules\ReadingList\Infrastructure\Resources;

use Modules\ReadingList\Domain\Entities\ReadingItem;

class ReadingItemResource
{
    public static function toArray(ReadingItem $item): array
    {
        return [
            'id' => $item->id,
            'user_id' => $item->userId,
            'title' => $item->title,
            'url' => $item->url,
            'domain' => $item->domain,
            'is_read' => $item->isRead,
            'is_favorite' => $item->isFavorite,
            'created_at' => $item->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $items): array
    {
        return array_map(fn (ReadingItem $item) => self::toArray($item), $items);
    }
}
