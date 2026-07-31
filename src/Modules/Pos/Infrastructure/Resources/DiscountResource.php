<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\Discount;

class DiscountResource
{
    public static function toArray(Discount $discount): array
    {
        return [
            'id' => $discount->id,
            'outlet_id' => $discount->outletId,
            'name' => $discount->name,
            'type' => $discount->type->value,
            'value' => $discount->value,
            'min_purchase' => $discount->minPurchase,
            'member_only' => $discount->memberOnly,
            'is_active' => $discount->isActive,
            'priority' => $discount->priority,
            'start_date' => $discount->startDate ? substr($discount->startDate, 0, 10) : null,
            'end_date' => $discount->endDate ? substr($discount->endDate, 0, 10) : null,
            'conditions' => $discount->conditions,
            'product_id' => $discount->productId,
        ];
    }

    public static function collection(array $discounts): array
    {
        return array_map(fn (Discount $discount) => self::toArray($discount), $discounts);
    }
}
