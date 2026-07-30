<?php

namespace Modules\Accounting\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Shared\Infrastructure\Traits\BelongsToUser;

class JournalEntryModel extends Model
{
    use BelongsToUser;

    protected $table = 'accounting_journal_entries';

    protected $fillable = [
        'user_id',
        'entry_number',
        'date',
        'description',
        'total_debit',
    ];

    protected function casts(): array
    {
        return [
            'entry_number' => 'integer',
            'date' => 'date',
            'total_debit' => 'decimal:2',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLineModel::class, 'journal_entry_id');
    }
}
