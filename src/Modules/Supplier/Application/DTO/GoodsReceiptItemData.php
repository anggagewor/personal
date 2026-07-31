<?php

namespace Modules\Supplier\Application\DTO;

readonly class GoodsReceiptItemData
{
    public function __construct(
        public int $purchaseOrderItemId,
        public int $productVariantId,
        public int $quantity,
    ) {}
}
