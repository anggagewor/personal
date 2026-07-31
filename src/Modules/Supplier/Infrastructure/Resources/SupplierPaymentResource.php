<?php

namespace Modules\Supplier\Infrastructure\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierPaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'purchase_order_id' => $this->resource->purchaseOrderId,
            'amount' => $this->resource->amount,
            'payment_date' => $this->resource->paymentDate,
            'payment_method' => $this->resource->paymentMethod->value,
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}
