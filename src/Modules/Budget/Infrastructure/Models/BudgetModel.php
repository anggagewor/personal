<?php

namespace Modules\Budget\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class BudgetModel extends Model
{
    use BelongsToUser;

    protected $table = 'budgets';

    protected $fillable = [
        'user_id',
        'category',
        'amount',
        'month',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
        ];
    }
}
