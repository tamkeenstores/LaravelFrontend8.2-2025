<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorStepCategories extends Model
{
    use HasFactory;
    protected $table = 'doorstepdelivery_categories';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
