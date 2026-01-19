<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCashBank extends Model
{
    protected $fillable = [
        'username',
        'password',
        'id_bank_tujuan'
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
        ];
    }
}
