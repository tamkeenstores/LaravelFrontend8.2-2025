<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowroomOrderDetail extends Model
{
    use HasFactory;
    protected $table = 'showroom_order_detail';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function orderData() {
        return $this->belongsTo(ShowroomOrder::class, 'order_id', 'id');
    }
    
    public function productData() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    
    
    public function replaceproductData() {
        return $this->belongsTo(Product::class, 'replace_id', 'id');
    }
    
    public function ShipmentOrder() {
        return $this->belongsTo(OrderShipment::class, 'order_id', 'id');
    }
}