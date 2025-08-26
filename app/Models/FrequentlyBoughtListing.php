<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrequentlyBoughtListing extends Model
{
    use HasFactory;
    protected $table = 'fbt_list';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'fbt_id', 'discount', 'product_id'
    ];
    
    public function productdetail(){
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
