<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Cviebrock\EloquentSluggable\Sluggable;

class Blog extends Model
{
    use HasFactory;
    // use Sluggable;


    protected $table = 'blog';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = [
        
        'id','category_id','tag_id','name', 'name_arabic', 'sub_heading','sub_heading_ar','slug', 'image', 'content', 'content_arabic', 'description', 'description_arabic', 'status','image_media', 'viewed_blog', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar', 'meta_description_en', 'meta_description_ar', 'meta_canonical_en', 'meta_canonical_ar','created_at','updated_at',
    ];
    
    public function BlogMediaImage() {
        return $this->belongsTo(ProductMedia::class, 'image_media', 'id');
    }
    
    public function subTagsData() {
        return $this->belongsToMany(SubTags::class, 'blog_tag', 'blog_id', 'tag_id');
    }
    public function categoriesData() {
        return $this->belongsToMany(Productcategory::class, 'blog_category', 'blog_id', 'category_id');
    }
    public function BrandsData() {
        return $this->belongsToMany(Brand::class, 'blog_brand', 'blog_id', 'brand_id');
    }
    // public function tags() {
    //     return $this->belongsToMany(Tag::class, 'blog_tag', 'blog_id', 'tag_id');
    // }

    // public function blog_categories() {
    //     return $this->belongsToMany(Category::class, 'blog_category', 'blog_id', 'category_id');
    // }

    // public function sluggable(): array
    //     {
    //         return [
    //             'slug' => [
    //                 'source' => 'name'
    //             ]
    //         ];
    //     }
        
    //     public function ImageMedia() {
    //         return $this->belongsTo(Media::class, 'image_media', 'id')->select(['id', 'file_url']);
    //     }
}
