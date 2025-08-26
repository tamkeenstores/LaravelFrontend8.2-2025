<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetailBanner extends Model
{
    use HasFactory;
    protected $table = 'product_detail_banner';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
        'id', 'end_date', 'start_date', 'status', 'type' , 'for_web', 'for_app','image_en_media','image_ar_media'
    ];

    public function ProductDetailBannerproducts() {
        return $this->hasMany(ProductDetailBanner_product::class, 'product_detail_banner_id', 'id');
    }

    public function ProductDetailBannerProductStore() {
        return $this->belongsToMany(ProductDetailBanner_product::class, 'product_detail_banner_product', 'product_detail_banner_id', 'product_id');
    }

    public function ProductDetailBannercategories() {
        return $this->hasMany(ProductDetailBanner_categories::class, 'product_detail_banner_id', 'id');
    }

    public function ProductDetailBannerCategoriesStore() {
        return $this->belongsToMany(ProductDetailBanner_categories::class, 'product_detail_banner_categories', 'product_detail_banner_id', 'category_id');
    }
    
    // public function ImageEnMedia() {
    //     return $this->belongsTo(Media::class, 'image_en_media', 'id');
    // }
    
    // public function ImageArMedia() {
    //     return $this->belongsTo(Media::class, 'image_ar_media', 'id');
    // }
}
