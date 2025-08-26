<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newslatter extends Model
{
    use HasFactory;
     protected $table = 'newslatters';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = ['id','email', 'status'];
}
