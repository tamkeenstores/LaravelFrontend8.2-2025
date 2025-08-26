<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HasColumnMany\UseColumnMany;

class SecondDbShipmentReschedule extends Model
{
    use HasFactory;
    use UseColumnMany;
    protected $table = 'shipment_reschedule';
    protected $guarded = [];
    protected $connection = 'second_db';
    
    public function rescheduleStatusShipment(){
        return $this->belongsTo(SecondDbShipmentRescheduleStatus::class,'status_id', 'id');
    }
    
    public function shipmentRescheduleStatusRider(){
        return $this->setConnection('mysql')->belongsTo(User::class, 'rider_id', 'employee_id');
    }
    
    public function shipmentOrder(){
        return $this->belongsTo(SecondDbOrderShipment::class, 'shipment_id', 'id');
    }
}
