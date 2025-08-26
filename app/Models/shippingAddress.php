<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class shippingAddress extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'shipping_address';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'country_id', 'customer_id', 'first_name', 'last_name', 'phone_number', 'city_id', 'state_id','area_id', 'zip', 'address', 'apartment_flat_number', 'address_option', 'addtional_address', 'shippinginstractions', 'make_default', 'address_label', 'deleted'
    ];

    public function stateData()
    {
        return $this->belongsTo(States::class, 'state_id', 'id');
    }
    
    public function userData()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
    
    public function orders() {
        return $this->hasMany(Order::class, 'shipping_id', 'id');   
    }
    
    public function areaData()
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }
}
