<?php

namespace Modules\Supplier\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProductModel extends Model
{
    protected $table = 'supplier_products';

    protected $fillable = [
        'supplier_id',
        'product_variant_id',
        'default_unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'default_unit_cost' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id');
    }
}
