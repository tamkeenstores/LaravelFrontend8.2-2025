<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusTimeLine extends Model
{
    use HasFactory;
    protected $table = 'order_status_timeline';
    protected $fillable = ['id', 'order_id', 'status','created_at', 'updated_at'];
    
    // protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function orderData()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
