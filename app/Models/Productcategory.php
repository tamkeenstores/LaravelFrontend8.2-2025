<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Productcategory extends Model
{
    use HasFactory;
    use Sluggable;
    protected $hidden = ['children'];

    protected $table = 'productcategories';
    protected $fillable = [
        'id',
        'name',
        'name_arabic',
        'name_app',
        'name_arabic_app',
        'slug',
        'parent_id',
        'menu',
        'description',     
        'description_arabic',     
        'status',     
        'meta_title_en',
        'meta_title_ar',
        'meta_description_en',
        'meta_description_ar',
        'meta_tag_en',
        'meta_tag_ar',
        'meta_canonical_en',
        'meta_canonical_ar',
        'sort',
        'web_image_media',
        'mobile_image_media',
        'not_for_export',
        'icon',
        'brand_link',
        'arabyads',
        'image_link_app',
        'clicks',
        'views',
        'created_at',
        'updated_at'
    ];
    
    public function category() {
        return $this->belongsTo(Productcategory::class, 'parent_id', 'id');
    }
    
    public function child() {
        return $this->hasMany(Productcategory::class, 'parent_id', 'id');
    }
    
    public function WebMediaImage() {
        return $this->belongsTo(ProductMedia::class, 'web_image_media', 'id');
    }
    
    public function MobileMediaAppImage() {
        return $this->belongsTo(ProductMedia::class, 'mobile_image_media', 'id');
    }
    public function FeatureImage() {
        return $this->belongsTo(ProductMedia::class, 'icon', 'id');
    }
    
    public function filtercategory() 
    {
        return $this->belongsToMany(SubTags::class, 'filter_category', 'category_id', 'filter_category_id');
    }
    
    public function bestSellerCategory() 
    {
        return $this->belongsToMany(Product::class, 'best_seller_category', 'category_id', 'product_id');
    }
    
    public function productname() {
        return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id');
    }
    
    public function productCount() {
        return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id')->where('status', 1)
        ->where('price', '>', 0)
        ->where('quantity', '>=', 1);
    }
    
    public function getProductCount() {
        return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id')
        ->where('status', 1);
        // ->where('price', '>', 0)
        // ->where('quantity', '>=', 1);
    }
    
    // public function productname() {
    //     return $this->hasMany(CategoryProduct::class, 'category_id', 'id');
    // }
    
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
}
