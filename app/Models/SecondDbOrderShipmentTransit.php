<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondDbOrderShipmentTransit extends Model
{
    use HasFactory;
    protected $table = 'order_shipment_transit';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
    
    public function warehouseData() {
        return $this->setConnection('mysql')->belongsTo(Warehouse::class, 'warehouse_id', 'ln_code');
    }
    
    public function riderData() {
        return $this->setConnection('mysql')->belongsTo(User::class, 'rider', 'employee_id');
    }
    
    public function ShipmentDeliveryImagesData() {
        return $this->hasMany(SecondDbShipmentDeliveryImage::class, 'transit_shipment_id', 'id');
    }
    
    public function ShipmentSignatureData() {
        return $this->hasMany(SecondDbShipmentSignature::class, 'transit_shipment_id', 'id');
    }
    
}
