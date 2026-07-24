<?php

namespace Modules\Task\Infrastructure\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class TaskModel extends Model
{
    use SoftDeletes, BelongsToUser, HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'position' => 'integer',
        ];
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderByDesc('created_at');
    }
}
