<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderShipmentTransit extends Model
{
    use HasFactory;
    protected $table = 'order_shipment_transit';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function warehouseData() {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }
    
    public function riderData() {
        return $this->belongsTo(User::class, 'rider', 'id');
    }
    
    public function ShipmentDeliveryImagesData() {
        return $this->hasMany(ShipmentDeliveryImage::class, 'transit_shipment_id', 'id');
    }
    
    public function ShipmentSignatureData() {
        return $this->hasMany(ShipmentSignature::class, 'transit_shipment_id', 'id');
    }
    
}
