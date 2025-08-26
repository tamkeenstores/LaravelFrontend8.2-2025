<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeGift extends Model
{
    use HasFactory;
     protected $table = 'free_gift';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'name', 'name_arabic', 'notes', 'free_gift_type', 'show_on', 'include_cities', 'exclude_cities', 'cities_restriction', 'discount_type',
    	'allowed_gifts', 'add_free_gift_item', 'allow_delete', 'amount_type', 'restriction_pages', 'min_amount', 'max_amount', 'usage_limit',
    	'usage_user_limit', 'start_date', 'end_date','status', 'image'
    ];
    
    public function products() 
    {
        return $this->belongsToMany(Product::class, 'free_gift_products', 'free_gift_id', 'product_id');
    }
    
    public function productcategory() {
        return $this->belongsToMany(Productcategory::class, 'free_gift_categories', 'free_gift_id', 'category_id');
    }
    public function tags() 
    {
        return $this->belongsToMany(SubTags::class, 'free_gift_tags', 'free_gift_id', 'sub_tag_id');
    }
    public function brands() 
    {
        return $this->belongsToMany(Brand::class, 'free_gift_brands', 'free_gift_id', 'brand_id');
    }
    
    public function freegiftlistproduct() 
    {
        return $this->belongsToMany(Product::class, 'free_gift_listing', 'free_gift_id', 'product_id');
    }
    public function freegiftlist() {
        return $this->hasMany(FreeGiftListing::class, 'free_gift_id', 'id');
    }
    
    public function orders() 
    {
        return $this->belongsToMany(Order::class, 'order_free_gift', 'free_gift_id', 'order_id');
    }
    
}
