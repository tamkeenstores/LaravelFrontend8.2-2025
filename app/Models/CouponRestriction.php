<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HasColumnMany\UseColumnMany;

class CouponRestriction extends Model
{
    use HasFactory;
    use UseColumnMany;
    protected $table = 'coupon_restriction';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function rulesData(){
        return $this->hasColumnMany(Rules::class, 'rules_id');
    }
    
    public function freegiftData(){
        return $this->hasColumnMany(FreeGift::class, 'free_gifts_id');
    }
    
    public function fbtData(){
        return $this->hasColumnMany(FrequentlyBoughtTogether::class, 'fbt_id');
    }
    
    // public function productData(){
    //     return $this->hasColumnMany(Product::class, 'product_id');
    // }
}
