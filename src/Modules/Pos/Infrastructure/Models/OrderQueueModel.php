<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderQueueModel extends Model
{
    protected $table = 'pos_order_queue';

    protected $fillable = [
        'table_session_id',
        'items',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
        ];
    }

    public function tableSession(): BelongsTo
    {
        return $this->belongsTo(TableSessionModel::class, 'table_session_id');
    }
}
