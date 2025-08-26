<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneFreeShippingDetails extends Model
{
    use HasFactory;
    protected $table = 'zone_free_shipping_details';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
