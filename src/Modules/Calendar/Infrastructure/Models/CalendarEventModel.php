<?php

namespace Modules\Calendar\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class CalendarEventModel extends Model
{
    use BelongsToUser, HasFactory;

    protected $table = 'calendar_events';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'color',
        'all_day',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'all_day' => 'boolean',
        ];
    }
}
