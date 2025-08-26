<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrequentlyBoughtTogether extends Model
{
    use HasFactory;
    protected $table = 'fbt';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'name', 'name_arabic', 'notes', 'fbt_type', 'show_on_product', 'show_on_cart', 'multi_quantity', 'include_cities', 'exclude_cities', 'cities_restriction',
    	'discount_type', 'amount_type', 'restriction_pages', 'min_amount', 'max_amount', 'usage_limit',
    	'usage_user_limit', 'start_date', 'end_date','status'
    ];
    
    public function products() 
    {
        return $this->belongsToMany(Product::class, 'fbt_products', 'fbt_id', 'product_id');
    }
    
    public function productcategory() {
        return $this->belongsToMany(Productcategory::class, 'fbt_categories', 'fbt_id', 'category_id');
    }
    public function tags() 
    {
        return $this->belongsToMany(SubTags::class, 'fbt_tags', 'fbt_id', 'sub_tag_id');
    }
    public function brands() 
    {
        return $this->belongsToMany(Brand::class, 'fbt_brands', 'fbt_id', 'brand_id');
    }
    public function fbtlist() {
        return $this->hasMany(FrequentlyBoughtListing::class, 'fbt_id', 'id');
    }
    
    public function orders() 
    {
        return $this->belongsToMany(Order::class, 'order_fbt', 'fbt_id', 'order_id');
    }
}
