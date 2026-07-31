<?php

namespace Modules\Supplier\Infrastructure\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'outlet_id' => $this->resource->outletId,
            'name' => $this->resource->name,
            'address' => $this->resource->address,
            'phone' => $this->resource->phone,
            'email' => $this->resource->email,
            'bank_name' => $this->resource->bankName,
            'bank_account_number' => $this->resource->bankAccountNumber,
            'bank_account_holder' => $this->resource->bankAccountHolder,
            'notes' => $this->resource->notes,
            'total_debt' => $this->resource->totalDebt,
            'created_at' => $this->resource->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->resource->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
