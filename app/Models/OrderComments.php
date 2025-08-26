<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderComments extends Model
{
    use HasFactory;
    protected $table = 'order_comments';
    
    // protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function orderData()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    
    public function UserDetail()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
}
