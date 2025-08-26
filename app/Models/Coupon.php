<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;
    protected $table = 'coupon';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function restrictions() {
        return $this->hasMany(CouponRestriction::class, 'coupon_id', 'id');
    }
    
    public function conditionsOnlyVoucher() {
        return $this->hasMany(RulesConditions::class, 'rule_id', 'id')->where('module_type', 1)->where('condition_type', 13);
    }
    
    public function conditions() {
        return $this->hasMany(RulesConditions::class, 'rule_id', 'id')->where('module_type', 1);
    }
    
    public function products() 
    {
        return $this->belongsToMany(Product::class, 'coupon_products', 'coupon_id', 'product_id');
    }
    
    public function category() 
    {
        return $this->belongsToMany(Productcategory::class, 'coupon_categories', 'coupon_id', 'category_id');
    }
    
    public function brands() 
    {
        return $this->belongsToMany(Brand::class, 'coupon_brand', 'coupon_id', 'brand_id');
    }
    
    public function subtags() 
    {
        return $this->belongsToMany(SubTags::class, 'coupon_subtags', 'coupon_id', 'sub_tag_id');
    }
    
    public function orders() {
        return $this->hasMany(OrderSummary::class, 'amount_id', 'id');
    }
    
    public function getRemainingDaysAttribute()
    {
        if ($this->end_date) {
            $remaining_days = Carbon::now()->diffInDays(Carbon::parse($this->end_date));
        } else {
            $remaining_days = 0;
        }
        return $remaining_days;
    }
    
}
