<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondDbShowroomOrderDetail extends Model
{
    use HasFactory;
    protected $table = 'showroom_order_detail';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
    
    public function orderData() {
        return $this->belongsTo(SecondDbShowroomOrder::class, 'order_id', 'id');
    }
    
    public function productData() {
        return $this->setConnection('mysql')->belongsTo(Product::class, 'product_id', 'id');
    }
    
    public function warehouseData() {
        return $this->setConnection('mysql')->belongsTo(Warehouse::class, 'warehouse', 'ln_code');
    }
    
    
    public function replaceproductData() {
        return $this->setConnection('mysql')->belongsTo(Product::class, 'replace_id', 'id');
    }
    
    public function ShipmentOrder() {
        return $this->belongsTo(SecondDbOrderShipment::class, 'order_id', 'id');
    }
}