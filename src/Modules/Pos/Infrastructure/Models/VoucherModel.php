<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoucherModel extends Model
{
    protected $table = 'pos_vouchers';

    protected $fillable = [
        'outlet_id',
        'code',
        'type',
        'value',
        'min_purchase',
        'usage_limit',
        'usage_count',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(OutletModel::class, 'outlet_id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemptionModel::class, 'voucher_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
