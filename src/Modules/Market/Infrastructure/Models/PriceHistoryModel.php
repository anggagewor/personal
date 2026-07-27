<?php

namespace Modules\Market\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistoryModel extends Model
{
    protected $table = 'market_price_history';

    protected $fillable = [
        'user_id',
        'symbol',
        'price',
        'change',
        'change_percent',
        'previous_close',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'change' => 'float',
            'change_percent' => 'float',
            'previous_close' => 'float',
            'fetched_at' => 'datetime',
        ];
    }
}
