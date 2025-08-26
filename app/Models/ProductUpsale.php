<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUpsale extends Model
{
    use HasFactory;
    protected $table = 'product_upsale';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'product_id', 'upsale_id', 'created_at', 'updated_at',
    ];
    
    public function product_upsale()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
