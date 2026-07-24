<?php

namespace Modules\Pomodoro\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class PomodoroModel extends Model
{
    use BelongsToUser, HasFactory;

    protected $table = 'pomodoros';

    protected $fillable = [
        'user_id',
        'task_id',
        'duration',
        'status',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
