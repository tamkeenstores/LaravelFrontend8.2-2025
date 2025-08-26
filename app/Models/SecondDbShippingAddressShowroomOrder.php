<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondDbShippingAddressShowroomOrder extends Model
{
    use HasFactory;
    protected $table = 'shipping_address_showroom_order';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
    
    public function stateData() {
        return $this->setConnection('mysql')->belongsTo(States::class, 'city', 'id');
    }
    
    public function areaData() {
        return $this->setConnection('mysql')->belongsTo(Area::class, 'area', 'id');
    }
    
    public function orders() {
        return $this->hasMany(SecondDbShowroomOrder::class, 'shipping_id', 'id');   
    }
}
