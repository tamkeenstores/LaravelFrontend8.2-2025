<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $table = 'role';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function permission()
    {
        return $this->hasMany(RolePermission::class);
    }
    
    public function users() {
        return $this->hasMany(User::class, 'role_id', 'id');   
    }
}
