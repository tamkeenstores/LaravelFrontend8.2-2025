<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFBT extends Model
{
    use HasFactory;
    protected $table = 'order_fbt';
    protected $fillable = ['id', 'order_id', 'fbt_id'];
}
