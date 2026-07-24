<?php

namespace Modules\Note\Infrastructure\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NoteModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notes';

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Infrastructure\Models\UserModel::class, 'user_id');
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }
}
