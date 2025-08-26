<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCardSettingImages extends Model
{
    use HasFactory;
    protected $table = 'gift_card_setting_images';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function giftCardImageData(){
        return $this->belongsTo(ProductMedia::class, 'image', 'id');
    }
}

