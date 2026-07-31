<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\ProductVariant;

class ProductVariantResource
{
    public static function toArray(ProductVariant $variant): array
    {
        return [
            'id' => $variant->id,
            'product_id' => $variant->productId,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'price' => $variant->price,
            'stock_quantity' => $variant->stockQuantity,
        ];
    }

    public static function collection(array $variants): array
    {
        return array_map(fn (ProductVariant $variant) => self::toArray($variant), $variants);
    }
}
