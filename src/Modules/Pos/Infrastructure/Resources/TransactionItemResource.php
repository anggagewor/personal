<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\TransactionItem;

class TransactionItemResource
{
    public static function toArray(TransactionItem $item): array
    {
        return [
            'id' => $item->id,
            'transaction_id' => $item->transactionId,
            'product_id' => $item->productId,
            'product_variant_id' => $item->productVariantId,
            'product_name' => $item->productName,
            'variant_name' => $item->variantName,
            'quantity' => $item->quantity,
            'unit_price' => $item->unitPrice,
            'subtotal' => $item->subtotal,
        ];
    }

    public static function collection(array $items): array
    {
        return array_map(fn (TransactionItem $item) => self::toArray($item), $items);
    }
}
