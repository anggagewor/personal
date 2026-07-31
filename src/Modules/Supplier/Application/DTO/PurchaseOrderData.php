<?php

namespace Modules\Supplier\Application\DTO;

readonly class PurchaseOrderData
{
    public function __construct(
        public int $supplierId,
        public string $orderDate,
        public ?string $expectedDeliveryDate = null,
        public ?string $notes = null,
        /** @var PurchaseOrderItemData[] */
        public array $items = [],
    ) {}
}
