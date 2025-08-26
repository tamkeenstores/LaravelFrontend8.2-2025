<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddressShowroomOrder extends Model
{
    use HasFactory;
    protected $table = 'shipping_address_showroom_order';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function stateData() {
        return $this->belongsTo(States::class, 'city', 'id');
    }
    
    public function orders() {
        return $this->hasMany(ShowroomOrder::class, 'shipping_id', 'id');   
    }
}
