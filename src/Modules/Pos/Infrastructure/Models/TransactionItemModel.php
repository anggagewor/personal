<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItemModel extends Model
{
    public $timestamps = false;

    protected $table = 'pos_transaction_items';

    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'quantity',
        'unit_price',
        'subtotal',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(TransactionModel::class, 'transaction_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariantModel::class, 'product_variant_id');
    }
}
