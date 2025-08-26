<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderSummary extends Model
{
    use HasFactory;
    protected $table = 'order_summary';
    
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function orderData()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    
    public function couponData()
    {
        return $this->hasMany(Coupon::class, 'id', 'amount_id');
    }
    
    public function ExpressData()
    {
        return $this->belongsTo(ExpressDelivery::class, 'amount_id', 'id');
    }
}
