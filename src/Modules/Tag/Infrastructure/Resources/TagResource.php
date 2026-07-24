<?php

namespace Modules\Tag\Infrastructure\Resources;

use Modules\Tag\Domain\Entities\Tag;

class TagResource
{
    public static function toArray(Tag $tag): array
    {
        return [
            'id' => $tag->id,
            'user_id' => $tag->userId,
            'name' => $tag->name,
            'color' => $tag->color,
        ];
    }

    public static function collection(array $tags): array
    {
        return array_map(fn (Tag $tag) => self::toArray($tag), $tags);
    }
}
