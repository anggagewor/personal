<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\Table;

class TableResource
{
    public static function toArray(Table $table): array
    {
        return [
            'id' => $table->id,
            'outlet_id' => $table->outletId,
            'name' => $table->name,
            'token' => $table->token,
            'is_active' => $table->isActive,
            'qr_url' => url("/pos/order/{$table->token}"),
        ];
    }

    public static function collection(array $tables): array
    {
        return array_map(fn (Table $table) => self::toArray($table), $tables);
    }
}
