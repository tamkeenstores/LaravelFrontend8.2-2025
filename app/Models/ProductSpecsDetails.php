<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpecsDetails extends Model
{
    use HasFactory;
    protected $table = 'product_specs_details';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'specs_id', 'specs_en', 'value_en', 'specs_ar', 'value_ar', 'created_at', 'updated_at',
    ];
}
