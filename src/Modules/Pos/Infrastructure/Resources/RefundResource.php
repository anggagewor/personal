<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\Refund;

class RefundResource
{
    public static function toArray(Refund $refund): array
    {
        return [
            'id' => $refund->id,
            'transaction_id' => $refund->transactionId,
            'refund_number' => $refund->refundNumber,
            'refund_amount' => $refund->refundAmount,
            'reason' => $refund->reason,
            'refund_method' => $refund->refundMethod,
            'items' => array_map(fn ($item) => [
                'id' => $item->id,
                'transaction_item_id' => $item->transactionItemId,
                'product_id' => $item->productId,
                'product_variant_id' => $item->productVariantId,
                'product_name' => $item->productName,
                'variant_name' => $item->variantName,
                'quantity' => $item->quantity,
                'unit_price' => $item->unitPrice,
                'refund_amount' => $item->refundAmount,
            ], $refund->items),
            'created_at' => $refund->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $refunds): array
    {
        return array_map(fn (Refund $refund) => self::toArray($refund), $refunds);
    }
}
