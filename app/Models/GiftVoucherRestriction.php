<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HasColumnMany\UseColumnMany;

class GiftVoucherRestriction extends Model
{
    use HasFactory;
    use UseColumnMany;
    protected $table = 'gift_voucher_restriction';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function rulesData(){
        return $this->hasColumnMany(Rules::class, 'rules_id');
    }
    
    public function freegiftsData(){
        return $this->hasColumnMany(FreeGift::class, 'free_gifts_id');
    }
    
    public function specialoffersData(){
        return $this->hasColumnMany(SpecialOffer::class, 'special_offers_id');
    }
    
    public function couponData(){
        return $this->hasColumnMany(Coupon::class, 'coupon_id');
    }
    
    public function fbtData(){
        return $this->hasColumnMany(FrequentlyBoughtTogether::class, 'fbt_id');
    }
    
    // public function brandsData(){
    //     return $this->hasColumnMany(Brand::class, 'brand_id');
    // }
    
    // public function categoriesData(){
    //     return $this->hasColumnMany(Productcategory::class, 'category_id');
    // }
    
    // public function subtagsData(){
    //     return $this->hasColumnMany(SubTags::class, 'sub_tag_id');
    // }
    
    // public function productData(){
    //     return $this->hasColumnMany(Product::class, 'product_id');
    // }
}
