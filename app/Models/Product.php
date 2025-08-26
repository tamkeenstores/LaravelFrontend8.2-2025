<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'products';
    // protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'name', 'name_arabic', 'short_description', 'short_description_arabic', 'description', 'description_arabic', 'slug', 'price', 'sale_price',
    	'sort', 'tax_status', 'tax_class', 'notes', 'sku','ln_sku', 'quantity','amazon_stock', 'in_stock','low_in_stock','top_selling', 'shipping_class', 'related_type', 'related_brands', 'related_categories', 'custom_badge_en',
    	'custom_badge_ar', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar', 'meta_canonical_en', 'meta_canonical_ar', 'meta_description_en',
    	'meta_description_ar', 'best_seller','free_gift','brands','feature_image','status', 'pre_order', 'no_of_days','mpn','created_at', 'updated_at', 'deleted_at',
    	'clicks', 'view_product', 'warranty','flixmedia','flixmedia_ar','vatonuspromo','trendyol_barcode','trendyol_quantity','trendyol_saleprice','trendyol_price','cdn_image','pormotion','pormotion_arabic','pormotion_color','badge_left','badge_left_arabic','badge_left_color','badge_right','badge_right_arabic','badge_right_color',
    	'promotional_price','promo_title','promo_title_arabic', 'product_video', 'flash_sale_price', 'flash_sale_expiry' , 'save_type', 'specification_image',
    	'specification_image_one', 'specification_image_two', 'specification_image_three', 'specification_image_four', 'specification_image_five' , 'specification_image_six' , 'gift_image' , 'hide_on_frontend', 'eligible_for_pickup'
    ];
    
    public function warehouseData(){
	   return $this->setConnection('second_db')->hasMany(LiveStock::class, 'sku', 'sku');
    }
    
    public function liveStockData(){
	   return $this->hasMany(LiveStock::class, 'sku', 'sku');
    }
    
    public function liveStockSum(){
	   return $this->hasMany(LiveStock::class, 'sku', 'sku')->selectRaw('livestock.sku,SUM(livestock.qty) as quantity') ->groupBy('livestock.sku');
	   //return $this->hasMany('App\Rating') ->selectRaw('ratingtablename.post_id,SUM(ratingtablenaem.score) as total') ->groupBy('ratingtablename.post_id');
    }
    
    public function specs() {
        return $this->hasMany(ProductSpecifications::class, 'product_id', 'id');
    }
    
    public function specsStore() 
    {
        return $this->belongsToMany(Product::class, 'products_specifications', 'product_id', 'id');
    }
    
    public function features() {
        return $this->hasMany(ProductFeatures::class, 'product_id', 'id');
    }
    
    public function featuresStore() 
    {
        return $this->belongsToMany(Product::class, 'products_key_features', 'product_id', 'id');
    }
    
    public function upsale() 
    {
        return $this->belongsToMany(Product::class, 'product_upsale', 'product_id', 'upsale_id');
    }
    
    public function tags() 
    {
        return $this->belongsToMany(SubTags::class, 'product_tag', 'product_id', 'sub_tag_id');
    }
    
    public function productcategory() {
        return $this->belongsToMany(Productcategory::class, 'product_categories', 'product_id', 'category_id');
    }
    
    public function questions() 
    {
        return $this->belongsToMany(ProductFaqs::class, 'product_questions', 'product_id', 'questions_id');
    }
    
    public function brand(){
        return $this->belongsTo(Brand::class, 'brands', 'id');
    }
    
    public function featuredImage(){
        return $this->belongsTo(ProductMedia::class, 'feature_image', 'id');
    }
    
    public function shippingclass(){
        return $this->belongsTo(ShippingClasses::class, 'shipping_class', 'id');
    }
    
    public function gallery() {
        return $this->hasMany(ProductGallery::class, 'product_id', 'id');
    }
    
    public function galleryStore() 
    {
        return $this->belongsToMany(Product::class, 'product_gallery', 'product_id', 'id');
    }
    
    public function productrelatedcategory() {
        return $this->belongsToMany(Productcategory::class, 'product_related_category', 'product_id', 'category_id');
    }
    
    public function productrelatedbrand() {
        return $this->belongsToMany(Brand::class, 'product_related_brands', 'product_id', 'brand_id');
    }
    
    public function reviews() {
        return $this->hasMany(ProductReview::class, 'product_sku', 'sku')->where('status',1);
    }
    
    public function multiFreeGiftData() {
        return $this->hasMany(ProductMultiFreeGift::class, 'product_id', 'id');
    }
}
