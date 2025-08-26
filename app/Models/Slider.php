<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;
    protected $table = 'sliders';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function featuredImageWeb(){
        return $this->belongsTo(ProductMedia::class, 'image_web', 'id');
    }
    
    public function featuredImageApp(){
        return $this->belongsTo(ProductMedia::class, 'image_mobile', 'id');
    }
    
    public function cat(){
        return $this->belongsTo(Productcategory::class, 'category_id', 'id');
    }
    
    public function pro(){
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    
    public function brand(){
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
    
    public function subtag(){
        return $this->belongsTo(SubTags::class, 'sub_tag_id', 'id');
    }
    
    public function setting(){
        return $this->belongsTo(SliderSetting::class);
    }
}
