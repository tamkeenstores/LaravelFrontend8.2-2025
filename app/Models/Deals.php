<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deals extends Model
{
    use HasFactory;
    protected $table = 'deals';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = [
        
        'id', 'start_date', 'end_date', 'sale_price', 'image', 'quantity', 'status', 'left_quantity', 'for_web', 'for_app','image_media'
    ];
    // public function dealsProduct(){
    //     return $this->hasMany(DealsProduct::class, 'deal_id', 'id');
    // }

    // public function Dealproducts() {
    //     return $this->belongsToMany(Product::class, 'deals_product', 'deal_id', 'product_id');
    // }
    
    // public function ImageMedia() {
    //     return $this->belongsTo(Media::class, 'image_media', 'id');
    // }
}
