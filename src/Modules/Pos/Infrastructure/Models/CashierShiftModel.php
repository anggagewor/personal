<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashierShiftModel extends Model
{
    protected $table = 'pos_cashier_shifts';

    protected $fillable = [
        'outlet_id',
        'user_id',
        'cashier_name',
        'opening_amount',
        'closing_amount',
        'expected_amount',
        'difference',
        'status',
        'notes',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_amount' => 'decimal:2',
            'closing_amount' => 'decimal:2',
            'expected_amount' => 'decimal:2',
            'difference' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(OutletModel::class, 'outlet_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TransactionModel::class, 'shift_id');
    }
}
