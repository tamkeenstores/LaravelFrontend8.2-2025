<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionPopup extends Model
{
    use HasFactory;
    protected $table = 'promotion_popup';

    protected $fillable =[
     
     'id', 'position', 'type', 'description', 'page', 'time', 'status', 'for_web', 'for_app','image_media'

    ];
    
    // public function ImageMedia() {
    //     return $this->belongsTo(Media::class, 'image_media', 'id');
    // }
}
