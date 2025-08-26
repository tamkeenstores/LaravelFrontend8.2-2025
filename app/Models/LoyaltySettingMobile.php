<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltySettingMobile extends Model
{
    use HasFactory;
    protected $table = 'loyalty_setting_mobile';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
