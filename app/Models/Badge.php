<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;
    protected $table = 'badge';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
        'id','title', 'title_arabic', 'end_date', 'start_date', 'discount', 'discount_arabic', 'status', 'badge_type', 'for_web', 'for_app','image_media'
    ];

    public function productcategory() {
        return $this->belongsToMany(Productcategory::class, 'badge_categories', 'badge_id', 'category_id');
    }
    
    public function products() 
    {
        return $this->belongsToMany(Product::class, 'badge_products', 'badge_id', 'product_id');
    }
    
    public function BadgeSlider() {
        return $this->belongsTo(ProductMedia::class, 'image_media', 'id');
    }
}
