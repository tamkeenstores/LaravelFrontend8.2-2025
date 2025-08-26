<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneShippingMethod extends Model
{
    use HasFactory;
    protected $table = 'zone_shipping_method';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function flat_classes() {
        return $this->hasMany(FlatRateClass::class, 'zone_shipping_id', 'id');
    }
    
    public function ZoneShipping() {
        return $this->belongsTo(ShippingZone::class, 'zone_id', 'id');
    }
}
