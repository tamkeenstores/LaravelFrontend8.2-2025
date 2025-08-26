<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notification';

    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable =[
        'id', 'title', 'title_arabic', 'message', 'message_arabic', 'image', 'date', 'link', 'type', 'product_id', 'brand_id', 'category_id', 'for_web', 'for_app', 'instant_notification','app_counts','web_counts'
    ];
}
