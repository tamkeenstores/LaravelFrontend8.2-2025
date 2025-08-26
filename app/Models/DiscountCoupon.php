<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCoupon extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'discount_coupon';
	protected $fillable = ['title','product_id', 'brand_id','product_category', 'minimum_order_amount', 'maximum_order_amount', 'payment_method', 'offer_product', 'offer_brand', 'offer_category', 'offer_expire', 'copun_expire', 'discount_type','discount', 'status', 'disable_gift_vouchers', 'disable_discount_rules'];
}
