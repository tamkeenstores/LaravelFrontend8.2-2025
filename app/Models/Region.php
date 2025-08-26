<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;
    protected $table = 'region';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = ['id','name','name_arabic','country', 'status', 'online_capacity', 'offline_capacity','created_at','updated_at'];
    
    public function citydata() 
    {
        return $this->belongsToMany(States::class, 'region_city', 'region_id', 'city_id');
    }
    
    public function cityname() {
        return $this->hasMany(States::class, 'region', 'id');   
    }
    
    public function storeData() {
        return $this->belongsTo(StoreLocator::class, 'id', 'region');
    }
    
    public function ordersData()
    {
        return $this->belongsToMany(Order::class, 'orders_region', 'region_id', 'order_id');
    }
}
