<?php

namespace Modules\Supplier\Infrastructure\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'supplier_id' => $this->resource->supplierId,
            'po_number' => $this->resource->poNumber,
            'order_date' => $this->resource->orderDate,
            'status' => $this->resource->status->value,
            'payment_status' => $this->resource->paymentStatus->value,
            'total_amount' => $this->resource->totalAmount,
        ];
    }
}
