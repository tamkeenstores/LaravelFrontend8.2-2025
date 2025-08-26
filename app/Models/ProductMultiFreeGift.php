<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMultiFreeGift extends Model
{
    use HasFactory;
    protected $table = 'product_multi_freegifts';

    protected $guarded = [];
    
    public function productData() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    
    public function productSkuData() {
        return $this->belongsTo(Product::class, 'free_gift_sku', 'sku');
    }
}
