<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifyProduct extends Model
{
    use HasFactory;
    protected $table = 'notify_product';

    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function productData() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
