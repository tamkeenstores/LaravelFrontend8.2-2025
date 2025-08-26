<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorStepBrand extends Model
{
    use HasFactory;
    protected $table = 'doorstepdelivery_brands';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
