<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\Voucher;

class VoucherResource
{
    public static function toArray(Voucher $voucher): array
    {
        return [
            'id' => $voucher->id,
            'outlet_id' => $voucher->outletId,
            'code' => $voucher->code,
            'type' => $voucher->type->value,
            'value' => $voucher->value,
            'min_purchase' => $voucher->minPurchase,
            'usage_limit' => $voucher->usageLimit,
            'usage_count' => $voucher->usageCount,
            'expires_at' => $voucher->expiresAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'is_active' => $voucher->isActive,
            'created_at' => $voucher->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $vouchers): array
    {
        return array_map(fn (Voucher $voucher) => self::toArray($voucher), $vouchers);
    }
}
