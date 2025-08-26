<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponCategory extends Model
{
    use HasFactory;
    protected $table = 'coupon_categories';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
