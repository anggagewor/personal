<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundItemModel extends Model
{
    protected $table = 'pos_refund_items';

    public $timestamps = false;

    protected $fillable = [
        'refund_id',
        'transaction_item_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'quantity',
        'unit_price',
        'refund_amount',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function refund(): BelongsTo
    {
        return $this->belongsTo(RefundModel::class, 'refund_id');
    }

    public function transactionItem(): BelongsTo
    {
        return $this->belongsTo(TransactionItemModel::class, 'transaction_item_id');
    }
}
