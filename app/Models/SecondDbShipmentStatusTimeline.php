<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondDbShipmentStatusTimeline extends Model
{
    use HasFactory;
    protected $table = 'shipment_status_timeline';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
    
    public function shipmentData()
    {
        return $this->belongsTo(SecondDbOrderShipment::class, 'shipment_id', 'id');
    }
}
