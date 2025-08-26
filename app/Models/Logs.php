<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    use HasFactory;
    protected $table = 'logs';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function userData() {
        return $this->hasMany(User::class, 'id', 'user_id');
    }
}
