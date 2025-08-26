<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileSetting extends Model
{
    use HasFactory;
    protected $table = 'mobile_setting';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function ImageEn() {
        return $this->belongsTo(ProductMedia::class, 'project_sale_image', 'id');
    }
    
    public function ImageAr() {
        return $this->belongsTo(ProductMedia::class, 'project_sale_image_arabic', 'id');
    }
}
