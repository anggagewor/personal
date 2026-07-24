<?php

namespace Modules\Bookmark\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class BookmarkModel extends Model
{
    use BelongsToUser, HasFactory;

    protected $table = 'bookmarks';

    protected $fillable = [
        'user_id',
        'title',
        'url',
        'description',
        'category',
        'icon',
    ];
}
