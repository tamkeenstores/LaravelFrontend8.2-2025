<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremiumMembership extends Model
{
    use HasFactory;
    protected $table = 'premium_membership';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
