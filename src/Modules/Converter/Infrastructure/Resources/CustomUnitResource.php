<?php

namespace Modules\Converter\Infrastructure\Resources;

use Modules\Converter\Domain\Entities\CustomUnit;

class CustomUnitResource
{
    public static function toArray(CustomUnit $unit): array
    {
        return [
            'id' => $unit->id,
            'category_id' => $unit->categoryId,
            'name' => $unit->name,
            'symbol' => $unit->symbol,
            'to_base' => $unit->toBase,
            'is_base' => $unit->isBase,
            'created_at' => $unit->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'updated_at' => $unit->updatedAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $units): array
    {
        return array_map(fn (CustomUnit $u) => self::toArray($u), $units);
    }
}
