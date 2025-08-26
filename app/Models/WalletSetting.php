<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletSetting extends Model
{
    use HasFactory;
    protected $table = 'wallet_setting';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
