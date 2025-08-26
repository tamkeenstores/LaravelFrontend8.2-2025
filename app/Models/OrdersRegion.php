<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersRegion extends Model
{
    use HasFactory;
    protected $table = 'orders_region';
    protected $fillable = ['id', 'order_id', 'region_id', 'created_at', 'updated_at'];

    public function orderData() 
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function regionData() 
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }
}
