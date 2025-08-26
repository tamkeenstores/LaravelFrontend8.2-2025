<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpressDelivery extends Model
{
    use HasFactory;
    protected $table = 'express_deliveries';
    protected $fillable = ['title', 'title_arabic', 'price', 'num_of_days', 'type', 'status'];
    protected $guard = ['id', 'created_at', 'updated_at'];
    
    public function citydata() 
    {
        return $this->belongsToMany(States::class, 'express_delivery_city', 'express_id', 'city_id');
    }
    
    public function productcategory() {
        return $this->belongsToMany(Productcategory::class, 'express_delivery_category', 'express_id', 'category_id');
    }
    public function brands() 
    {
        return $this->belongsToMany(Brand::class, 'express_delivery_brand', 'express_id', 'brand_id');
    }
    
    public function products() 
    {
        return $this->belongsToMany(Product::class, 'express_delivery_product', 'express_id', 'product_id');
    }
}
