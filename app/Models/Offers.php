<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offers extends Model
{
    use HasFactory;
    protected $table = 'offers';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable =['name','name_arabic','subtitle','subtitle_arabic','button_title','button_title_arabic','button_slug','button_id','viewtype','section_id','type','product_ids','brand_ids','category_ids','boxcolor','textcolor','btncolor','status'];
    
    // public function Products() 
    // {
    //     return $this->belongsToMany(Product::class, 'offers_products', 'offer_id', 'product_id');
    // }
    // public function Brands() 
    // {
    //     return $this->belongsToMany(Brand::class, 'offers_brands', 'offer_id', 'brand_id');
    // }
    // public function Categories() 
    // {
    //     return $this->belongsToMany(Productcategory::class, 'offers_categories', 'offer_id', 'category_id');
    // }
}
