<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorStepProduct extends Model
{
    use HasFactory;
    protected $table = 'doorstepdelivery_products';
    protected $fillable =[
     'id', 'doorstep_id', 'product_id',
    ];
}
