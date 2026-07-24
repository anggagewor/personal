<?php

namespace Modules\Finance\Infrastructure\Resources;

use Modules\Finance\Domain\Entities\Finance;

class FinanceResource
{
    public static function toArray(Finance $finance): array
    {
        return [
            'id' => $finance->id,
            'user_id' => $finance->userId,
            'type' => $finance->type->value,
            'amount' => $finance->amount,
            'category' => $finance->category,
            'description' => $finance->description,
            'date' => $finance->date?->format('Y-m-d'),
            'created_at' => $finance->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $finances): array
    {
        return array_map(fn (Finance $finance) => self::toArray($finance), $finances);
    }
}
