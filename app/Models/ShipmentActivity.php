<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentActivity extends Model
{
    use HasFactory;
    protected $table = 'shipment_activity';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function shipment() {
        return $this->belongsTo(OrderShipment::class, 'shipment_id', 'id');
    }
}