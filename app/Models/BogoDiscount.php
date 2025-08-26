<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HasColumnMany\UseColumnMany;

class BogoDiscount extends Model
{
    use HasFactory;
    use UseColumnMany;
    protected $table = 'bogo_discount';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function productData(){
        return $this->hasColumnMany(Product::class, 'products_id');
    }
    
    public function orders() 
    {
        return $this->belongsToMany(Order::class, 'order_bogo', 'bogo_id', 'order_id');
    }
}
