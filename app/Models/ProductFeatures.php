<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFeatures extends Model
{
    use HasFactory;
    protected $table = 'products_key_features';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'product_id', 'feature_en', 'feature_ar', 'feature_image_link', 'created_at', 'updated_at',
    ];
}
