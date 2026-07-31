<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariantModel extends Model
{
    protected $table = 'pos_product_variants';

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'stock_quantity',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustmentModel::class, 'product_variant_id');
    }
}
