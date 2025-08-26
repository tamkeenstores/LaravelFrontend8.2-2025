<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftVoucherSms extends Model
{
    use HasFactory;
    protected $table = 'gift_voucher_sms';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
