<?php

namespace Modules\Supplier\Infrastructure\Repositories;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Modules\Supplier\Application\DTO\GoodsReceiptData;
use Modules\Supplier\Domain\Contracts\GoodsReceiptRepositoryInterface;
use Modules\Supplier\Domain\Entities\GoodsReceipt;
use Modules\Supplier\Domain\Entities\GoodsReceiptItem;
use Modules\Supplier\Infrastructure\Models\GoodsReceiptItemModel;
use Modules\Supplier\Infrastructure\Models\GoodsReceiptModel;

class EloquentGoodsReceiptRepository implements GoodsReceiptRepositoryInterface
{
    /**
     * @return GoodsReceipt[]
     */
    public function findByPurchaseOrder(int $purchaseOrderId): array
    {
        $models = GoodsReceiptModel::with('items')
            ->where('purchase_order_id', $purchaseOrderId)
            ->orderByDesc('receipt_date')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->all();
    }

    public function create(int $purchaseOrderId, GoodsReceiptData $data): GoodsReceipt
    {
        return DB::transaction(function () use ($purchaseOrderId, $data) {
            $model = GoodsReceiptModel::create([
                'purchase_order_id' => $purchaseOrderId,
                'receipt_date' => $data->receiptDate,
                'notes' => $data->notes,
            ]);

            foreach ($data->items as $item) {
                GoodsReceiptItemModel::create([
                    'goods_receipt_id' => $model->id,
                    'purchase_order_item_id' => $item->purchaseOrderItemId,
                    'product_variant_id' => $item->productVariantId,
                    'quantity' => $item->quantity,
                ]);
            }

            return $this->toEntity($model->fresh()->load('items'));
        });
    }

    public function getTotalReceivedByItem(int $purchaseOrderItemId): int
    {
        return (int) GoodsReceiptItemModel::where('purchase_order_item_id', $purchaseOrderItemId)
            ->sum('quantity');
    }

    private function toEntity(GoodsReceiptModel $model): GoodsReceipt
    {
        $items = $model->items->map(fn ($itemModel) => new GoodsReceiptItem(
            id: $itemModel->id,
            goodsReceiptId: $model->id,
            purchaseOrderItemId: $itemModel->purchase_order_item_id,
            productVariantId: $itemModel->product_variant_id,
            quantity: (int) $itemModel->quantity,
        ))->all();

        return new GoodsReceipt(
            id: $model->id,
            purchaseOrderId: $model->purchase_order_id,
            receiptDate: $model->receipt_date->format('Y-m-d'),
            notes: $model->notes,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            items: $items,
        );
    }
}
