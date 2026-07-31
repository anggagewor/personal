<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableModel extends Model
{
    protected $table = 'pos_tables';

    protected $fillable = [
        'outlet_id',
        'name',
        'token',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(OutletModel::class, 'outlet_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TableSessionModel::class, 'table_id');
    }
}
