<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAlert extends Model
{
    use HasFactory;
    protected $table = 'stock_alert';

    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function productData() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    
    public function UserData() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
