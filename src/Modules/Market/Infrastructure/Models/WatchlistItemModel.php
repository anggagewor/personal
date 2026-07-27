<?php

namespace Modules\Market\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class WatchlistItemModel extends Model
{
    protected $table = 'market_watchlist_items';

    protected $fillable = [
        'user_id',
        'symbol',
        'type',
        'label',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }
}
