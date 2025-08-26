<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;
    protected $table = 'maintenance';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = ['order_no', 'orderdetail_id', 'product_id', 'subject', 'comment','time', 'user_id','first_name','last_name','phone_number','brand_name','notes','ticket_id','radio_value'];
    
    public function OrderData()
    {
        return $this->belongsTo(Order::class, 'order_no', 'id');
    }
    
    public function UserData()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function ProData()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
