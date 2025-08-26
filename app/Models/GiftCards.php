<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCards extends Model
{
    use HasFactory;
    protected $table = 'gift_cards';
    protected $fillable = ['id', 'userid', 'giftcard_no', 'name', 'email', 'phonenumber','myself', 'amount','paymentmethod', 'paymentid','status', 'mobileapp'];
}
