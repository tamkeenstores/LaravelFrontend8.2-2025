<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondDbShipmentActivity extends Model
{
    use HasFactory;
    protected $table = 'shipment_activity';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
    
    public function user() {
        return $this->setConnection('mysql')->belongsTo(User::class, 'user_id', 'employee_id');
    }
    
    public function shipment() {
        return $this->belongsTo(SecondDbOrderShipment::class, 'shipment_id', 'id');
    }
}