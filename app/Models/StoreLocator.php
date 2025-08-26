<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreLocator extends Model
{
    use HasFactory;
    protected $table = 'store_locator_address';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function featuredImageWeb(){
        return $this->belongsTo(ProductMedia::class, 'image_media', 'id');
    }
    
    public function storeRegions(){
        return $this->belongsTo(Region::class, 'region', 'id');
    }
    
    public function storeCity(){
        return $this->belongsTo(States::class, 'waybill_city', 'id');
    }
    
    public function cities() 
    {
        return $this->belongsToMany(States::class, 'storelocator_city', 'store_id', 'city_id');
    }
}
