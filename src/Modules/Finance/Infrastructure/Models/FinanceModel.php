<?php

namespace Modules\Finance\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class FinanceModel extends Model
{
    use BelongsToUser, HasFactory;

    protected $table = 'finances';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'category',
        'description',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'date' => 'date',
        ];
    }
}
