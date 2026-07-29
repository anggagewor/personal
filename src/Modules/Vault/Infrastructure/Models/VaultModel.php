<?php

namespace Modules\Vault\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class VaultModel extends Model
{
    use BelongsToUser;

    protected $table = 'vault_entries';

    protected $fillable = [
        'user_id',
        'name',
        'username',
        'encrypted_password',
        'url',
        'notes',
        'category',
    ];
}
