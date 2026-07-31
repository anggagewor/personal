<?php

namespace Modules\Pos\Infrastructure\Resources;

use Illuminate\Support\Facades\Storage;
use Modules\Pos\Domain\Entities\Product;

class ProductResource
{
    public static function toArray(Product $product): array
    {
        return [
            'id' => $product->id,
            'outlet_id' => $product->outletId,
            'category_id' => $product->categoryId,
            'name' => $product->name,
            'base_price' => $product->basePrice,
            'sku' => $product->sku,
            'image' => $product->image ? Storage::disk('public')->url($product->image) : null,
            'has_variants' => $product->hasVariants,
            'track_stock' => $product->trackStock,
            'status' => $product->status->value,
            'variants' => ProductVariantResource::collection($product->variants),
        ];
    }

    public static function collection(array $products): array
    {
        return array_map(fn (Product $product) => self::toArray($product), $products);
    }
}
