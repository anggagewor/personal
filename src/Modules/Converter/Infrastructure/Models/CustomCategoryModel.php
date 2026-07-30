<?php

namespace Modules\Converter\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class CustomCategoryModel extends Model
{
    use BelongsToUser;

    protected $table = 'converter_categories';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'icon',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(CustomUnitModel::class, 'category_id');
    }
}
