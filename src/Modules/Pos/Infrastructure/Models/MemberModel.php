<?php

namespace Modules\Pos\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberModel extends Model
{
    protected $table = 'pos_members';

    protected $fillable = [
        'outlet_id',
        'name',
        'phone',
        'email',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(OutletModel::class, 'outlet_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TransactionModel::class, 'member_id');
    }
}
