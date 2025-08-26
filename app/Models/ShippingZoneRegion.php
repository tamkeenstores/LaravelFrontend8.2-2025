<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZoneRegion extends Model
{
    use HasFactory;
    protected $table = 'shipping_zone_region';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function city() {
        return $this->belongsTo(States::class, 'zone_region', 'id');
    }

    public function ShippingZoneregion() {
        return $this->hasMany(ShippingZone::class, 'id', 'shipping_zone_id');
    }
    public function shipLocation() {
        return $this->hasMany(ShippingLocation::class, 'id', 'zone_region');
    }
}
