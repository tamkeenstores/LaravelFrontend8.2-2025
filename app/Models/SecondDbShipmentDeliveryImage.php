<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentDeliveryImage extends Model
{
    use HasFactory;
    protected $table = 'shipment_delivery_image';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
}
