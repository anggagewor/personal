<?php

namespace Modules\Supplier\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceiptModel extends Model
{
    const UPDATED_AT = null;

    protected $table = 'supplier_goods_receipts';

    protected $fillable = [
        'purchase_order_id',
        'receipt_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'receipt_date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderModel::class, 'purchase_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItemModel::class, 'goods_receipt_id');
    }
}
