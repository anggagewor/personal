<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethodModel extends Model
{
    protected $table = 'pos_payment_methods';

    protected $fillable = [
        'outlet_id',
        'type',
        'name',
        'is_active',
        'settings',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(OutletModel::class, 'outlet_id');
    }
}
