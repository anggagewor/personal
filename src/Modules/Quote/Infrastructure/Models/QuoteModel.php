<?php

namespace Modules\Quote\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteModel extends Model
{
    use HasFactory;

    protected $table = 'quotes';

    public $timestamps = false;

    protected $fillable = [
        'content',
        'author',
    ];
}
