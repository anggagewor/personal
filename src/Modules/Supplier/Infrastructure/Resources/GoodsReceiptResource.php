<?php

namespace Modules\Supplier\Infrastructure\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GoodsReceiptResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'purchase_order_id' => $this->resource->purchaseOrderId,
            'receipt_date' => $this->resource->receiptDate,
            'notes' => $this->resource->notes,
            'items' => collect($this->resource->items)->map(fn ($item) => [
                'id' => $item->id,
                'purchase_order_item_id' => $item->purchaseOrderItemId,
                'product_variant_id' => $item->productVariantId,
                'quantity' => $item->quantity,
            ])->all(),
            'created_at' => $this->resource->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}
