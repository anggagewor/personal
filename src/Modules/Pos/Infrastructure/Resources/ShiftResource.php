<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\CashierShift;

class ShiftResource
{
    public static function toArray(CashierShift $shift): array
    {
        return [
            'id' => $shift->id,
            'outlet_id' => $shift->outletId,
            'user_id' => $shift->userId,
            'cashier_name' => $shift->cashierName,
            'opening_amount' => $shift->openingAmount,
            'closing_amount' => $shift->closingAmount,
            'expected_amount' => $shift->expectedAmount,
            'difference' => $shift->difference,
            'status' => $shift->status->value,
            'notes' => $shift->notes,
            'opened_at' => $shift->openedAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'closed_at' => $shift->closedAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $shifts): array
    {
        return array_map(fn (CashierShift $shift) => self::toArray($shift), $shifts);
    }
}
