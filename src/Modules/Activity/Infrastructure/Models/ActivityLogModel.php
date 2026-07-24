<?php

namespace Modules\Activity\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class ActivityLogModel extends Model
{
    use BelongsToUser, HasFactory;

    protected $table = 'activity_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
