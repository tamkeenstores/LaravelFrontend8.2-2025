<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeGiftBadgeProduct extends Model
{
    use HasFactory;
    protected $table = 'free_gift_badge_product';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
        'id', 'product_id', 'badge_id' 
    ];
}
