<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherRedemptionModel extends Model
{
    public $timestamps = false;

    protected $table = 'pos_voucher_redemptions';

    protected $fillable = [
        'voucher_id',
        'transaction_id',
        'discount_amount',
        'redeemed_at',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'redeemed_at' => 'datetime',
        ];
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(VoucherModel::class, 'voucher_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(TransactionModel::class, 'transaction_id');
    }
}
