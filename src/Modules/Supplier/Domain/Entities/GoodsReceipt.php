<?php

namespace Modules\Supplier\Domain\Entities;

use DateTimeImmutable;

class GoodsReceipt
{
    public function __construct(
        public ?int $id,
        public int $purchaseOrderId,
        public string $receiptDate,
        public ?string $notes = null,
        public ?DateTimeImmutable $createdAt = null,
        /** @var GoodsReceiptItem[] */
        public array $items = [],
    ) {}
}
