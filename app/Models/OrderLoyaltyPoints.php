<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLoyaltyPoints extends Model
{
    use HasFactory;
    protected $table = 'order_loyaltypoints';
    protected $fillable = ['id', 'order_id', 'loyaltypoints_id'];
}
