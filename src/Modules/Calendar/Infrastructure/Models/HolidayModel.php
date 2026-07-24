<?php

namespace Modules\Calendar\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayModel extends Model
{
    use HasFactory;

    protected $table = 'holidays';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'date',
        'type',
        'is_national',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_national' => 'boolean',
        ];
    }
}
