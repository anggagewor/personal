<?php

namespace Modules\Supplier\Infrastructure\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'product_variant_id' => $this->resource->productVariantId,
            'product_name' => $this->resource->productName,
            'variant_name' => $this->resource->variantName,
            'quantity' => $this->resource->quantity,
            'unit_cost' => $this->resource->unitCost,
            'subtotal' => $this->resource->subtotal,
            'received_quantity' => $this->resource->receivedQuantity,
        ];
    }
}
