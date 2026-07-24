<?php

namespace Modules\Shared\Infrastructure\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToUser
{
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
