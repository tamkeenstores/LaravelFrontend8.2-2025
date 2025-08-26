<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceCenter extends Model
{
    use HasFactory;
    protected $table = 'maintenance_center';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function featuredImageWeb(){
        return $this->belongsTo(ProductMedia::class, 'image_media', 'id');
    }
    
    public function maintenanceCenterRegions(){
        return $this->belongsTo(Region::class, 'region', 'id');
    }
    
    public function cityData() 
    {
        return $this->belongsTo(States::class, 'city', 'id');
    }
    
    public function multiCityData() 
    {
        return $this->belongsToMany(States::class, 'maintenance_center_city', 'maintenance_center_id', 'id');
    }
}
