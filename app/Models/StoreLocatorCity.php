<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreLocatorCity extends Model
{
    use HasFactory;
    protected $table = 'storelocator_city';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
