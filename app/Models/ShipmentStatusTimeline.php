<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentStatusTimeline extends Model
{
    use HasFactory;
    protected $table = 'shipment_status_timeline';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function shipmentData()
    {
        return $this->belongsTo(OrderShipment::class, 'shipment_id', 'id');
    }
}
