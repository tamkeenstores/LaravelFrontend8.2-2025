<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderGiftVouchers extends Model
{
    use HasFactory;
    protected $table = 'order_giftvouchers';
    protected $fillable = ['id', 'order_id', 'giftvouchers_id'];
}