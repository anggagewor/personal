<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefundModel extends Model
{
    protected $table = 'pos_refunds';

    protected $fillable = [
        'transaction_id',
        'refund_number',
        'refund_amount',
        'reason',
        'refund_method',
    ];

    protected function casts(): array
    {
        return [
            'refund_amount' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(TransactionModel::class, 'transaction_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RefundItemModel::class, 'refund_id');
    }
}
