<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponSubTag extends Model
{
    use HasFactory;
    protected $table = 'coupon_subtags';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
