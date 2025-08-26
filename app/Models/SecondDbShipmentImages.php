<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondDbShipmentImages extends Model
{
    use HasFactory;
    protected $table = 'shipment_images';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
}
