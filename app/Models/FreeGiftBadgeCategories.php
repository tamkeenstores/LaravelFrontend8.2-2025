<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeGiftBadgeCategories extends Model
{
    use HasFactory;
    protected $table = 'free_gift_badge_categories';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
        'id', 'category_id', 'badge_id' 
    ];
}
