<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderBogo extends Model
{
    use HasFactory;
    protected $table = 'order_bogo';
    protected $fillable = ['id', 'order_id', 'bogo_id', 'created_at', 'updated_at'];
}
