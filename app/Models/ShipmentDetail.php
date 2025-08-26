<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentDetail extends Model
{
    use HasFactory;
    protected $table = 'shipment_detail';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function detailData() {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id', 'id');
    }
    
    public function replaceproductData() {
        return $this->belongsTo(Product::class, 'replace_id', 'id');
    }
    
    public function ShipmentOrder() {
        return $this->belongsTo(OrderShipment::class, 'shipment_id', 'id');
    }
}
