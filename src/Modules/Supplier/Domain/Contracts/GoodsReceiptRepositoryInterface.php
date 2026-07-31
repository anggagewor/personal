<?php

namespace Modules\Supplier\Domain\Contracts;

use Modules\Supplier\Application\DTO\GoodsReceiptData;
use Modules\Supplier\Domain\Entities\GoodsReceipt;

interface GoodsReceiptRepositoryInterface
{
    /**
     * @return GoodsReceipt[]
     */
    public function findByPurchaseOrder(int $purchaseOrderId): array;

    public function create(int $purchaseOrderId, GoodsReceiptData $data): GoodsReceipt;

    public function getTotalReceivedByItem(int $purchaseOrderItemId): int;
}
