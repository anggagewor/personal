<?php

namespace Modules\Converter\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomUnitModel extends Model
{
    protected $table = 'converter_units';

    protected $fillable = [
        'category_id',
        'name',
        'symbol',
        'to_base',
        'is_base',
    ];

    protected $casts = [
        'to_base' => 'float',
        'is_base' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CustomCategoryModel::class, 'category_id');
    }
}
