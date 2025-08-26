<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintainenceDisProducts extends Model
{
    use HasFactory;
    protected $table = 'maintainence_disabled_products';

    protected $guarded = ['id', 'created_at', 'updated_at'];
}
