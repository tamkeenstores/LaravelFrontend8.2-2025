<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateMarketing extends Model
{
    use HasFactory;
    protected $table = 'affiliate_marketing';
    protected $fillable = ['name', 'email', 'city', 'additional_information', 'code', 'type', 'page', 'status'];
    protected $guard = ['id', 'created_at', 'updated_at'];
    
    public function city() {
        return $this->belongsTo(States::class, 'city', 'id');
    }
    
    public function productcategory() {
        return $this->belongsToMany(Productcategory::class, 'affiliate_category', 'affiliate_id', 'category_id');
    }
    public function brands() 
    {
        return $this->belongsToMany(Brand::class, 'affiliate_brands', 'affiliate_id', 'brand_id');
    }
    
    public function products() 
    {
        return $this->belongsToMany(Product::class, 'affiliate_products', 'affiliate_id', 'product_id');
    }
}
