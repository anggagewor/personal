<?php

namespace Modules\GoogleDrive\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class DriveConnectionModel extends Model
{
    use BelongsToUser;

    protected $table = 'drive_connections';

    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'email',
        'token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
        ];
    }
}
