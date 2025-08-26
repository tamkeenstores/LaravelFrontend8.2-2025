<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondDbShipmentComments extends Model
{
    use HasFactory;
    protected $table = 'shipment_comments';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
    
    public function shipmentData()
    {
        return $this->belongsTo(SecondDbOrderShipment::class, 'shipment_id', 'id');
    }
    
    public function UserDetail()
    {
        return $this->setConnection('mysql')->belongsTo(User::class, 'user_id', 'employee_id');
    }
}
