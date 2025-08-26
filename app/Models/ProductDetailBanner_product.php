<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetailBanner_product extends Model
{
    use HasFactory;
     protected $table = 'product_detail_banner_product';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
        'id','product_detail_banner_id' ,'product_id'
    ];
}
