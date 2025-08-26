<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionBanner extends Model
{
    use HasFactory;
    protected $table = 'promotion_banner';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
        'id', 'end_date', 'start_date', 'image', 'status', 'type' , 'link' ,'category_link', 'for_web', 'for_app','image_media'
    ];

    public function PromotionBannerproducts() {
        return $this->hasMany(PromotionBannerProduct::class, 'promotion_banner_id', 'id');
    }

    // public function PromotionBannerStore() {
    //     return $this->belongsToMany(PromotionBannerProduct::class, 'promotion_banner_product', 'promotion_banner_id', 'product_id');
    // }

    public function PromotionBannercategories() {
        return $this->hasMany(PromotionBannerCategories::class, 'promotion_banner_id', 'id');
    }

    // public function categoriesStore() {
    //     return $this->belongsToMany(PromotionBannerCategories::class, 'promotion_banner_categories', 'promotion_banner_id', 'category_id');
    // }
    
    // public function ImageMedia() {
    //     return $this->belongsTo(Media::class, 'image_media', 'id')->select(['id', 'file_url']);
    // }
}
