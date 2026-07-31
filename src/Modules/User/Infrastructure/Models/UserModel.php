<?php

namespace Modules\User\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    public function getPreferencesWithDefaults(): array
    {
        $defaults = [
            'theme' => 'system',
            'primary_color' => 'indigo',
            'locale' => 'id',
            'sidebar_collapsed' => false,
            'timezones' => [],
        ];

        return array_merge($defaults, $this->preferences ?? []);
    }

}
