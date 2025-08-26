<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStockHistory extends Model
{
    use HasFactory;
    protected $table = 'product_stock_history';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
