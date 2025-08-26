<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplaceProduct extends Model
{
    use HasFactory;
    protected $table = 'replace_product';
    protected $fillable = ['name', 'name_arabic', 'description', 'description_arabic', 'replace_type', 'status'];
    protected $guard = ['id', 'created_at', 'updated_at'];
    
    public function productcategory() {
        return $this->belongsToMany(Productcategory::class, 'replace_cat', 'replace_id', 'category_id');
    }
    
    public function products() 
    {
        return $this->belongsToMany(Product::class, 'replace_pro', 'replace_id', 'product_id');
    }
    
    public function brand() 
    {
        return $this->belongsToMany(Brand::class, 'replace_brand', 'replace_id', 'brand_id');
    }
    
    public function subtag() 
    {
        return $this->belongsToMany(SubTags::class, 'replace_tag', 'replace_id', 'sub_tag_id');
    }
}
