<?php

namespace Modules\Supplier\Application\Actions\GoodsReceipt;

use Illuminate\Support\Facades\DB;
use Modules\Pos\Application\Actions\Catalog\AdjustStockAction;
use Modules\Pos\Application\DTO\StockAdjustmentData;
use Modules\Supplier\Application\DTO\GoodsReceiptData;
use Modules\Supplier\Domain\Contracts\GoodsReceiptRepositoryInterface;
use Modules\Supplier\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Supplier\Domain\Entities\GoodsReceipt;
use Modules\Supplier\Domain\Enums\PurchaseOrderStatus;
use Modules\Supplier\Domain\Exceptions\InvalidPurchaseOrderStateException;
use Modules\Supplier\Domain\Exceptions\OverDeliveryException;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderItemModel;

class CreateGoodsReceiptAction
{
    public function __construct(
        private PurchaseOrderRepositoryInterface $poRepository,
        private GoodsReceiptRepositoryInterface $receiptRepository,
        private AdjustStockAction $adjustStockAction,
    ) {}

    public function execute(int $purchaseOrderId, GoodsReceiptData $data): GoodsReceipt
    {
        $po = $this->poRepository->findById($purchaseOrderId);

        // 1. Validate PO can receive goods (confirmed or partial only)
        if (!$po->canReceiveGoods()) {
            throw InvalidPurchaseOrderStateException::notAllowed(
                $po->poNumber,
                'receive_goods',
                $po->status->value,
            );
        }

        // 2. Validate no over-delivery for each item
        foreach ($data->items as $receiptItem) {
            $poItem = collect($po->items)->first(
                fn ($item) => $item->id === $receiptItem->purchaseOrderItemId,
            );

            $totalReceived = $this->receiptRepository->getTotalReceivedByItem($poItem->id);
            $remaining = $poItem->quantity - $totalReceived;

            if ($receiptItem->quantity > $remaining) {
                throw OverDeliveryException::create(
                    $poItem->productName,
                    $remaining,
                    $receiptItem->quantity,
                );
            }
        }

        return DB::transaction(function () use ($purchaseOrderId, $data, $po) {
            // 3. Create goods receipt
            $receipt = $this->receiptRepository->create($purchaseOrderId, $data);

            // 4. Update received_quantity on PO items
            foreach ($data->items as $receiptItem) {
                $totalReceived = $this->receiptRepository->getTotalReceivedByItem(
                    $receiptItem->purchaseOrderItemId,
                );

                PurchaseOrderItemModel::where('id', $receiptItem->purchaseOrderItemId)
                    ->update(['received_quantity' => $totalReceived]);
            }

            // 5. Determine and update PO status
            $updatedPo = $this->poRepository->findById($purchaseOrderId);
            $allReceived = collect($updatedPo->items)->every(
                fn ($item) => $item->isFullyReceived(),
            );

            if ($allReceived) {
                $this->poRepository->updateStatus($purchaseOrderId, PurchaseOrderStatus::Received);
            } else {
                $this->poRepository->updateStatus($purchaseOrderId, PurchaseOrderStatus::Partial);
            }

            // 6. Create POS stock adjustments (type: restock, reference PO)
            foreach ($data->items as $receiptItem) {
                $this->adjustStockAction->execute(new StockAdjustmentData(
                    productVariantId: $receiptItem->productVariantId,
                    type: 'restock',
                    quantity: $receiptItem->quantity,
                    reason: "Penerimaan barang dari PO {$po->poNumber}",
                ));
            }

            return $receipt;
        });
    }
}
