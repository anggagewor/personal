<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionModel extends Model
{
    protected $table = 'pos_transactions';

    protected $fillable = [
        'outlet_id',
        'transaction_number',
        'subtotal',
        'discount_amount',
        'total',
        'payment_method',
        'payment_method_type',
        'amount_tendered',
        'change_amount',
        'status',
        'void_reason',
        'voided_at',
        'member_id',
        'table_session_id',
        'voucher_code',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_tendered' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'voided_at' => 'datetime',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(OutletModel::class, 'outlet_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItemModel::class, 'transaction_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberModel::class, 'member_id');
    }

    public function tableSession(): BelongsTo
    {
        return $this->belongsTo(TableSessionModel::class, 'table_session_id');
    }

    public function voucherRedemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemptionModel::class, 'transaction_id');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(TransactionDiscountModel::class, 'transaction_id');
    }
}
