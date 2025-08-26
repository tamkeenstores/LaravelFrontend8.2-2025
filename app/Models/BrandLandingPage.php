<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandLandingPage extends Model
{
    use HasFactory;
    protected $table = 'brand_landing_page';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function categories() {
        return $this->hasMany(BrandPageCategories::class, 'brand_landing_id', 'id');
    }
    
    public function categoriesNew() 
    {
        return $this->belongsToMany(Productcategory::class, 'brand_page_categories', 'brand_landing_id', 'category_id');
    }
    
    public function branddata() {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
    
    public function BrandBannerImage() {
        return $this->belongsTo(ProductMedia::class, 'brand_banner_media', 'id');
    }
    
    public function MiddleBannerImage() {
        return $this->belongsTo(ProductMedia::class, 'middle_banner_media', 'id');
    }
}
