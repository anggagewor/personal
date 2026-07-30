<?php

namespace Modules\Converter\Infrastructure\Resources;

use Modules\Converter\Domain\Entities\CustomCategory;

class CustomCategoryResource
{
    public static function toArray(CustomCategory $category): array
    {
        return [
            'id' => $category->id,
            'user_id' => $category->userId,
            'name' => $category->name,
            'description' => $category->description,
            'icon' => $category->icon,
            'created_at' => $category->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'updated_at' => $category->updatedAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }
}
