<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryModel extends Model
{
    protected $table = 'pos_categories';

    protected $fillable = [
        'outlet_id',
        'parent_id',
        'name',
        'icon',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'parent_id' => 'integer',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(OutletModel::class, 'outlet_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CategoryModel::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'category_id');
    }
}
