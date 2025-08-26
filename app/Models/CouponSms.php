<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponSms extends Model
{
    use HasFactory;
    protected $table = 'coupon_sms';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
