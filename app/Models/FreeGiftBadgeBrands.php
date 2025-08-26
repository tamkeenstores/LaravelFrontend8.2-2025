<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeGiftBadgeBrands extends Model
{
    use HasFactory;
    protected $table = 'free_gift_badge_brands';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
        'id','badge_id' ,'brand_id'
    ];
}
