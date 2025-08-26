<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRelatedBrand extends Model
{
    use HasFactory;
    protected $table = 'product_related_brands';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = ['product_id','brand_id'];
}
