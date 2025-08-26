<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyHistory extends Model
{
    use HasFactory;
    protected $table = 'loyalty_histories';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function UserDetail()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function orderData()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
