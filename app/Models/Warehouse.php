<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $table = 'warehouse';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function featuredImageWeb(){
        return $this->belongsTo(ProductMedia::class, 'image_media', 'id');
    }
    
    public function warehouseRegions(){
        return $this->belongsTo(Region::class, 'region', 'id');
    }
    
    public function cityData() 
    {
        return $this->belongsToMany(States::class, 'warehouse_city', 'warehouse_id', 'city_id');
    }
    
    public function showroomData(){
        return $this->belongsTo(StoreLocator::class, 'store_id', 'id');
    }
    
    public function livestockData() {
        return $this->hasMany(LiveStock::class, 'city', 'ln_code');
    }
    
    public function waybillCityData(){
        return $this->belongsTo(States::class, 'waybill_city', 'id');
    }
}
