<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialOffer extends Model
{
    use HasFactory;
    protected $table = 'special_offer';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // public function products() {
    //     return $this->hasMany(Product::class, 'special_offer', 'id');
    // }
    public function categoryData(){
        return $this->belongsTo(Productcategory::class, 'category_id', 'id')->select(['id', 'name', 'name_arabic']);
    }
}
