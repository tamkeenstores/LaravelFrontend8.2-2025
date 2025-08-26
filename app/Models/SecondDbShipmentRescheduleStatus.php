<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HasColumnMany\UseColumnMany;

class SecondDbShipmentRescheduleStatus extends Model
{
    use HasFactory;
    use UseColumnMany;
    protected $table = 'shipment_reschedule_status';
    protected $guarded = [];
    protected $connection = 'second_db';
    
    public function shipmentReschedule(){
        return $this->hasMany(SecondDbShipmentReschedule::class,'shipment_id');
    }
}
