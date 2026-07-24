<?php

namespace Modules\Journal\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class JournalModel extends Model
{
    use BelongsToUser, HasFactory;

    protected $table = 'journals';

    protected $fillable = [
        'user_id',
        'content',
        'mood',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
