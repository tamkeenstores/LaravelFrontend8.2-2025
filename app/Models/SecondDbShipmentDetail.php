<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentDetail extends Model
{
    use HasFactory;
    protected $table = 'shipment_detail';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
    
    public function detailData() {
        return $this->belongsTo(SecondDbShipmentDetail::class, 'order_detail_id', 'id');
    }
    
    public function replaceproductData() {
        return $this->setConnection('mysql')->belongsTo(Product::class, 'replace_id', 'id');
    }
    
    public function ShipmentOrder() {
        return $this->belongsTo(SecondDbOrderShipment::class, 'shipment_id', 'id');
    }
}
