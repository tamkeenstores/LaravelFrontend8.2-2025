<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeGiftListing extends Model
{
    use HasFactory;
    protected $table = 'free_gift_listing';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'free_gift_id', 'discount', 'product_id'
    ];
    
    public function productdetail(){
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
