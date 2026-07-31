<?php

namespace Modules\Supplier\Domain\Entities;

class GoodsReceiptItem
{
    public function __construct(
        public ?int $id,
        public int $goodsReceiptId,
        public int $purchaseOrderItemId,
        public int $productVariantId,
        public int $quantity,
    ) {}
}
