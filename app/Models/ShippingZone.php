<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    use HasFactory;
    protected $table = 'shipping_zone';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function city() {
        return $this->belongsTo(States::class, 'zone_region', 'id');
    }

    public function flatRate() {
        return $this->hasMany(FlatRateClass::class, 'zone_shipping_id', 'id');
    }
    
    public function shippingZoneRegion() {
        return $this->hasMany(ShippingZoneRegion::class, 'shipping_zone_id', 'id');
    }

    public function methods() {
        return $this->hasMany(ZoneShippingMethod::class, 'zone_id', 'id');
    }
    
    public function ZoneShip() {
        return $this->hasMany(ZoneShippingMethod::class, 'zone_id', 'id')->where('shipping_type', 0);
    }
}
