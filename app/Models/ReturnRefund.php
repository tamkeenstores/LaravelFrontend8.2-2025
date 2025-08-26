<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnRefund extends Model
{
    use HasFactory;
    protected $table = 'return_refund';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function products() {
        return $this->hasMany(ReturnProducts::class, 'return_id', 'id');
    }
    
    public function reasons() {
        return $this->hasMany(ReturnReasons::class, 'return_id', 'id');
    }
    
    public function UserData()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function OrderData()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
