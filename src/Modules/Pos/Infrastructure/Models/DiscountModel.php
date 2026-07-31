<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountModel extends Model
{
    protected $table = 'pos_discounts';

    protected $fillable = [
        'outlet_id',
        'name',
        'type',
        'value',
        'min_purchase',
        'member_only',
        'is_active',
        'priority',
        'starts_at',
        'ends_at',
        'conditions',
        'product_id',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'member_only' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'conditions' => 'array',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(OutletModel::class, 'outlet_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
