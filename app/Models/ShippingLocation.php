<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingLocation extends Model
{
    use HasFactory;
    protected $table = 'shipping_location';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = [
        'id', 'region', 'city', 'samsa', 'aramex', 'naqeel', 'flow', 'country', 'status',
    ];
    
    public function region() {
        return $this->belongsTo(Region::class, 'region', 'id');
    }
    
    public function city() {
        return $this->belongsTo(States::class, 'city', 'id');
    }
}
