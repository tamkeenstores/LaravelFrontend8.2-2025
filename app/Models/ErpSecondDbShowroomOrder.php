<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSecondDbShowroomOrder extends Model
{
    use HasFactory;
    protected $table = 'showroom_orders';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
    
}
