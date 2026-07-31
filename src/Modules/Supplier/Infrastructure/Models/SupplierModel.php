<?php

namespace Modules\Supplier\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierModel extends Model
{
    use SoftDeletes;

    protected $table = 'supplier_suppliers';

    protected $fillable = [
        'outlet_id',
        'name',
        'address',
        'phone',
        'email',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'notes',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrderModel::class, 'supplier_id');
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProductModel::class, 'supplier_id');
    }
}
