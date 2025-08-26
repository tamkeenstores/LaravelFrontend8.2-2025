<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPriceHistory extends Model
{
    use HasFactory;
    protected $table = 'product_price_history';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
