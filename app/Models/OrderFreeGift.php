<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFreeGift extends Model
{
    use HasFactory;
    protected $table = 'order_free_gift';
    protected $fillable = ['id', 'order_id', 'free_gift_id'];
}
