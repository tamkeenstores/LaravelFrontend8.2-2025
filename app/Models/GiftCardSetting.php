<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCardSetting extends Model
{
    use HasFactory;
    protected $table = 'gift_card_setting';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function giftCardAmounts() {
        return $this->hasMany(GiftCardSettingAmounts::class, 'gift_card_setting_id', 'id');
    }
    
    public function giftCardImages() {
        return $this->hasMany(GiftCardSettingImages::class, 'gift_card_setting_id', 'id');
    }
}
