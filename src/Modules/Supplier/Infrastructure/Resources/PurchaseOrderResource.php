<?php

namespace Modules\Supplier\Infrastructure\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'outlet_id' => $this->resource->outletId,
            'supplier_id' => $this->resource->supplierId,
            'po_number' => $this->resource->poNumber,
            'order_date' => $this->resource->orderDate,
            'expected_delivery_date' => $this->resource->expectedDeliveryDate,
            'status' => $this->resource->status->value,
            'payment_status' => $this->resource->paymentStatus->value,
            'total_amount' => $this->resource->totalAmount,
            'total_paid' => $this->resource->totalPaid ?? 0,
            'outstanding_balance' => $this->resource->outstandingBalance ?? 0,
            'notes' => $this->resource->notes,
            'cancelled_at' => $this->resource->cancelledAt?->format('Y-m-d H:i:s'),
            'items' => PurchaseOrderItemResource::collection($this->resource->items),
            'created_at' => $this->resource->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->resource->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
