<?php

namespace Modules\Wishlist\Infrastructure\Resources;

use Modules\Wishlist\Domain\Entities\WishlistItem;

class WishlistItemResource
{
    public static function toArray(WishlistItem $item): array
    {
        return [
            'id' => $item->id,
            'user_id' => $item->userId,
            'title' => $item->title,
            'description' => $item->description,
            'category' => $item->category,
            'is_completed' => $item->isCompleted,
            'completed_at' => $item->completedAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'created_at' => $item->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $items): array
    {
        return array_map(fn (WishlistItem $item) => self::toArray($item), $items);
    }
}
