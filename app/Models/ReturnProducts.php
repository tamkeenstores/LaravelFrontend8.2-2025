<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnProducts extends Model
{
    use HasFactory;
    protected $table = 'return_products';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function OrderDetailData()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id', 'id');
    }
    
    public function ProductData()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
