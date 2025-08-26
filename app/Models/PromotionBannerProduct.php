<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionBannerProduct extends Model
{
    use HasFactory;
    protected $table = 'promotion_banner_product';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
        'id','promotion_banner_id' ,'product_id'
    ];
}
