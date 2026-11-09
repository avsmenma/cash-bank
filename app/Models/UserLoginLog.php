<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginLog extends Model
{
    use HasFactory;

    protected $table = 'user_login_logs';

    protected $fillable = [
        'user_id',
        'username',
        'role',
        'ip_address',
        'user_agent',
        'login_at',
        'login_date',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'login_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
