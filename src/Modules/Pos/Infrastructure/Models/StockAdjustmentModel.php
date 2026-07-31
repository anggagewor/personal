<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentModel extends Model
{
    public $timestamps = false;

    protected $table = 'pos_stock_adjustments';

    protected $fillable = [
        'product_variant_id',
        'type',
        'quantity',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariantModel::class, 'product_variant_id');
    }
}
