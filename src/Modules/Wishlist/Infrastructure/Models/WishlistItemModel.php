<?php

namespace Modules\Wishlist\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class WishlistItemModel extends Model
{
    use BelongsToUser, HasFactory;

    protected $table = 'wishlists';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'is_completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }
}
