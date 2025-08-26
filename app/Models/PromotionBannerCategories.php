<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionBannerCategories extends Model
{
    use HasFactory;
    protected $table = 'promotion_banner_categories';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
        'id', 'category_id', 'promotion_banner_id' 
    ];
}
