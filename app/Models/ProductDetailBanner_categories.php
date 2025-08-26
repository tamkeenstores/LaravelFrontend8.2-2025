<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetailBanner_categories extends Model
{
    use HasFactory;
    protected $table = 'product_detail_banner_categories';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
        'id', 'category_id', 'product_detail_banner_id' 
    ];
}
