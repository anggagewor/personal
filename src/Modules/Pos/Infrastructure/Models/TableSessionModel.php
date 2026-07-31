<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableSessionModel extends Model
{
    protected $table = 'pos_table_sessions';

    protected $fillable = [
        'table_id',
        'status',
        'started_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(TableModel::class, 'table_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OrderQueueModel::class, 'table_session_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TransactionModel::class, 'table_session_id');
    }
}
