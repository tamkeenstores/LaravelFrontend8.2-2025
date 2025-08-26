<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltySetting extends Model
{
    use HasFactory;
    protected $table = 'loyalty_setting';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function settingdesktop() {
        return $this->hasMany(LoyaltySettingDesktop::class, 'loyaltysetting_id', 'id');
    }
    
    public function settingmobile() {
        return $this->hasMany(LoyaltySettingMobile::class, 'loyaltysetting_id', 'id');
    }
}
