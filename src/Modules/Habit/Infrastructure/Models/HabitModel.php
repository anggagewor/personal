<?php

namespace Modules\Habit\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class HabitModel extends Model
{
    use BelongsToUser, HasFactory;

    protected $table = 'habits';

    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'color',
        'frequency',
        'is_active',
        'current_streak',
        'longest_streak',
        'last_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'last_completed_at' => 'datetime',
        ];
    }
}
