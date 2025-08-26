<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandPageCategories extends Model
{
    use HasFactory;
    protected $table = 'brand_page_categories';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function Category() {
        return $this->belongsTo(Productcategory::class, 'category_id', 'id');
    }
    
    public function FeaturedImage() {
        return $this->belongsTo(ProductMedia::class, 'feature_image', 'id');
    }
}
