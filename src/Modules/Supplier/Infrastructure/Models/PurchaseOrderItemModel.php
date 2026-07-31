<?php

namespace Modules\Supplier\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItemModel extends Model
{
    public $timestamps = false;

    protected $table = 'supplier_purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'quantity',
        'unit_cost',
        'subtotal',
        'received_quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'received_quantity' => 'integer',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderModel::class, 'purchase_order_id');
    }

    public function goodsReceiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItemModel::class, 'purchase_order_item_id');
    }
}
