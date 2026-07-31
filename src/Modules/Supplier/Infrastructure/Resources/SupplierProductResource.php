<?php

namespace Modules\Supplier\Infrastructure\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'supplier_id' => $this->resource->supplierId,
            'product_variant_id' => $this->resource->productVariantId,
            'product_name' => $this->resource->productName,
            'variant_name' => $this->resource->variantName,
            'default_unit_cost' => $this->resource->defaultUnitCost,
        ];
    }
}
