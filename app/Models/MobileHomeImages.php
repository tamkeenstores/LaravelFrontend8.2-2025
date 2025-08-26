<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileHomeImages extends Model
{
    use HasFactory;
    protected $table = 'mobile_home_images';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function FeaturedImage() {
        return $this->belongsTo(ProductMedia::class, 'image', 'id');
    }
    
    public function FeaturedImageArabic() {
        return $this->belongsTo(ProductMedia::class, 'image_arabic', 'id');
    }
}
