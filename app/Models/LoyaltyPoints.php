<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPoints extends Model
{
    use HasFactory;
    protected $table = 'loyalty_points';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
}
