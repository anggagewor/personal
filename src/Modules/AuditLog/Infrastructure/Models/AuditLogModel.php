<?php

namespace Modules\AuditLog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class AuditLogModel extends Model
{
    use BelongsToUser;

    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'method',
        'ip_address',
        'user_agent',
        'tags',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
