<?php

namespace Modules\Bookmark\Infrastructure\Resources;

use Modules\Bookmark\Domain\Entities\Bookmark;

class BookmarkResource
{
    public static function toArray(Bookmark $bookmark): array
    {
        return [
            'id' => $bookmark->id,
            'user_id' => $bookmark->userId,
            'title' => $bookmark->title,
            'url' => $bookmark->url,
            'description' => $bookmark->description,
            'category' => $bookmark->category,
            'icon' => $bookmark->icon,
            'created_at' => $bookmark->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'updated_at' => $bookmark->updatedAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $bookmarks): array
    {
        return array_map(fn (Bookmark $bookmark) => self::toArray($bookmark), $bookmarks);
    }
}
