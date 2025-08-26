<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Cviebrock\EloquentSluggable\Sluggable;

class ShippingClasses extends Model
{
    use HasFactory;
    // use Sluggable;
    protected $table = 'shipping_classes';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'name', 'name_arabic', 'slug', 'description', 'description_arabic', 'created_at', 'updated_at',
    ];

    // public function region() {
    //     return $this->belongsTo(ShippingClasses::class, 'zone_region', 'id');
    // }

    // public function productsCount() {
    //     return $this->hasMany(Product::class, 'shipping_class', 'id');
    // }
    public function products() {
        return $this->hasMany(Product::class, 'shipping_class', 'id');
    }
}
