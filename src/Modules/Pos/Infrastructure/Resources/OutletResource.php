<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\Outlet;

class OutletResource
{
    public static function toArray(Outlet $outlet): array
    {
        return [
            'id' => $outlet->id,
            'name' => $outlet->name,
            'business_type' => $outlet->businessType->value,
            'payment_flow' => $outlet->paymentFlow->value,
            'address' => $outlet->address,
            'phone' => $outlet->phone,
            'settings' => $outlet->settings,
            'created_at' => $outlet->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $outlets): array
    {
        return array_map(fn (Outlet $outlet) => self::toArray($outlet), $outlets);
    }
}
