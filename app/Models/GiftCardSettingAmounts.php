<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCardSettingAmounts extends Model
{
    use HasFactory;
    protected $table = 'gift_card_setting_amounts';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}

