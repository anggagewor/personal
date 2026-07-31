<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductModel extends Model
{
    protected $table = 'pos_products';

    protected $fillable = [
        'outlet_id',
        'category_id',
        'name',
        'base_price',
        'sku',
        'image',
        'has_variants',
        'track_stock',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'has_variants' => 'boolean',
            'track_stock' => 'boolean',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(OutletModel::class, 'outlet_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariantModel::class, 'product_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
