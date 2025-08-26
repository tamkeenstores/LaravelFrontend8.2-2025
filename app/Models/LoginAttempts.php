<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempts extends Model
{
    use HasFactory;
    protected $table = 'login_attempts';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
