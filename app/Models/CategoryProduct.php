<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryProduct extends Model
{
    use HasFactory;
    protected $table = 'product_categories';

    protected $fillable =[
    	'id', 'product_id', 'category_id'
    ];
    
    // public function productdetail() {
    // 	return $this->belongsToMany(Product::class, 'product_category', 'product_id', 'category_id');
    // }
}
