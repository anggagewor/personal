<?php

namespace Modules\Accounting\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class AccountModel extends Model
{
    use BelongsToUser;

    protected $table = 'accounting_accounts';

    protected $fillable = [
        'user_id',
        'code',
        'name',
        'type',
        'normal_balance',
        'parent_id',
        'depth',
    ];

    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'parent_id' => 'integer',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(AccountModel::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(AccountModel::class, 'parent_id');
    }
}
