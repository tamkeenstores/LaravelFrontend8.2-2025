<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSliders extends Model
{
    use HasFactory;
    protected $table = 'app_sliders';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function ImageEn() {
        return $this->belongsTo(ProductMedia::class, 'image', 'id');
    }
    
    public function ImageAr() {
        return $this->belongsTo(ProductMedia::class, 'image_arabic', 'id');
    }
    
    public function ProductData() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    
    public function BrandData() {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
    
    public function CategoryData() {
        return $this->belongsTo(Productcategory::class, 'category_id', 'id');
    }
    
    public function TagData() {
        return $this->belongsTo(SubTags::class, 'tag_id', 'id');
    }
}
