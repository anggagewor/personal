<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\Category;

class CategoryResource
{
    public static function toArray(Category $category): array
    {
        return [
            'id' => $category->id,
            'outlet_id' => $category->outletId,
            'parent_id' => $category->parentId,
            'name' => $category->name,
            'icon' => $category->icon,
            'sort_order' => $category->sortOrder,
        ];
    }

    public static function collection(array $categories): array
    {
        return array_map(fn (Category $category) => self::toArray($category), $categories);
    }
}
