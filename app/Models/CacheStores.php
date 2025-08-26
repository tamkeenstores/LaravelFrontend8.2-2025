<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CacheStores extends Model
{
    use HasFactory;
    
    protected $table = 'cache_stores';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
