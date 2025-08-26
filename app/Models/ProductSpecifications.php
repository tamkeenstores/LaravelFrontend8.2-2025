<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpecifications extends Model
{
    use HasFactory;
    protected $table = 'products_specifications';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'product_id', 'heading_en', 'heading_ar', 'created_at', 'updated_at',
    ];
    
    public function specdetails() {
        return $this->hasMany(ProductSpecsDetails::class, 'specs_id', 'id');
    }
}
