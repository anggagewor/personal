<?php

namespace Modules\Goal\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class GoalModel extends Model
{
    use BelongsToUser, HasFactory;

    protected $table = 'goals';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'target_date',
        'status',
        'progress',
        'milestones',
    ];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'progress' => 'integer',
            'milestones' => 'array',
        ];
    }
}
