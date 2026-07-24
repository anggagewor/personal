<?php

namespace Modules\ReadingList\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class ReadingItemModel extends Model
{
    use BelongsToUser, HasFactory;

    protected $table = 'reading_list';

    protected $fillable = [
        'user_id',
        'title',
        'url',
        'description',
        'thumbnail',
        'domain',
        'is_read',
        'is_favorite',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_favorite' => 'boolean',
        ];
    }
}
