<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponBrand extends Model
{
    use HasFactory;
    protected $table = 'coupon_brand';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
