<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogSetting extends Model
{
    use HasFactory;
    protected $table = 'blog_setting';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = [
        'id', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar', 'meta_canonical_en', 'meta_canonical_ar', 'meta_description_en', 'meta_description_ar','slider_image',
    ];
    
    public function SliderImage() {
        return $this->belongsTo(ProductMedia::class, 'slider_image', 'id');
    }
}
