<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDiscountModel extends Model
{
    protected $table = 'pos_transaction_discounts';

    public $timestamps = false;

    protected $fillable = [
        'transaction_id',
        'discount_id',
        'name',
        'type',
        'value',
        'discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(TransactionModel::class, 'transaction_id');
    }
}
