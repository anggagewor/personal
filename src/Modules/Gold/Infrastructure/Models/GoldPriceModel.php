<?php

namespace Modules\Gold\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class GoldPriceModel extends Model
{
    protected $table = 'gold_prices';

    protected $fillable = [
        'date',
        'price',
        'change',
        'change_percent',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'price' => 'integer',
            'change' => 'integer',
            'change_percent' => 'float',
        ];
    }
}
