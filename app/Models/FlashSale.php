<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    use HasFactory;
    protected $table = 'flash_sale';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function restrictions() {
        return $this->hasMany(FlashRestriction::class, 'flash_id', 'id');
    }
    
    public function featuredImage(){
        return $this->belongsTo(ProductMedia::class, 'image', 'id');
    }
    
    public function featuredImageApp(){
        return $this->belongsTo(ProductMedia::class, 'image_app', 'id');
    }
    
    public function redirectionbrand(){
        return $this->belongsTo(Brand::class, 'redirection_brands', 'id');
    }
    
    public function redirectiontag(){
        return $this->belongsTo(SubTags::class, 'redirection_tags', 'id');
    }
    
    public function redirectionproduct(){
        return $this->belongsTo(Product::class, 'redirection_products', 'id');
    }
    
    public function redirectioncategory(){
        return $this->belongsTo(Productcategory::class, 'redirection_categories', 'id');
    }
}
