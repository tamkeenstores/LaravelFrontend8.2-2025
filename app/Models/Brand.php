<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Sluggable;
     
    protected $table = 'brands';
    // protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = ['name', 'name_arabic', 'slug', 'sorting', 'status','show_as_popular', 'description', 'description_arabic','meta_title_en', 'meta_title_ar',
        'meta_description_en',
        'meta_description_ar',
        'meta_tag_en',
        'meta_tag_ar',
        'meta_canonical_en', 'meta_canonical_ar','brand_image_media','brand_app_image_media', 'id', 'created_at', 'updated_at', 'clicks']; 
        
        
    public function BrandMediaImage() {
        return $this->belongsTo(ProductMedia::class, 'brand_image_media', 'id');
    }
    
    public function BrandMediaAppImage() {
        return $this->belongsTo(ProductMedia::class, 'brand_app_image_media', 'id');
    }
    
    public function category() 
    {
        return $this->belongsToMany(Productcategory::class, 'brand_category', 'brand_id', 'category_id');
    }
    
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    // public function products() {
    //     return $this->hasMany(Product::class, 'brands', 'id');
    // }
    
    public function productname() {
        return $this->hasMany(Product::class, 'brands', 'id');   
    }
    
    // public function productcount() {
    //     return $this->productname()->count();   
    // }
    
    
}
