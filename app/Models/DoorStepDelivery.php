<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorStepDelivery extends Model
{
    use HasFactory;
    protected $table = 'door_step_delivery';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'title', 'title_arabic', 'price', 'type', 'status'
    ];
    
    public function productcategory() {
        return $this->belongsToMany(Productcategory::class, 'doorstepdelivery_categories', 'doorstep_id', 'category_id');
    }
    public function brands() 
    {
        return $this->belongsToMany(Brand::class, 'doorstepdelivery_brands', 'doorstep_id', 'brand_id');
    }
    
    public function products() 
    {
        return $this->belongsToMany(Product::class, 'doorstepdelivery_products', 'doorstep_id', 'product_id');
    }
}
