<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class OutletModel extends Model
{
    use BelongsToUser, SoftDeletes;

    protected $table = 'pos_outlets';

    protected $fillable = [
        'user_id',
        'name',
        'business_type',
        'payment_flow',
        'address',
        'phone',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Infrastructure\Models\UserModel::class, 'user_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(CategoryModel::class, 'outlet_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'outlet_id');
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethodModel::class, 'outlet_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TransactionModel::class, 'outlet_id');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(DiscountModel::class, 'outlet_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(VoucherModel::class, 'outlet_id');
    }

    public function tables(): HasMany
    {
        return $this->hasMany(TableModel::class, 'outlet_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(MemberModel::class, 'outlet_id');
    }
}
