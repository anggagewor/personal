<?php

namespace Modules\Scratchpad\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class ScratchpadModel extends Model
{
    use BelongsToUser, HasFactory;

    protected $table = 'scratchpads';

    protected $fillable = [
        'user_id',
        'content',
        'color',
        'position',
    ];
}
