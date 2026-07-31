<?php

namespace Modules\Supplier\Application\DTO;

readonly class GoodsReceiptData
{
    /**
     * @param GoodsReceiptItemData[] $items
     */
    public function __construct(
        public string $receiptDate,
        public ?string $notes,
        public array $items,
    ) {}
}
