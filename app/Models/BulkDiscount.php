<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkDiscount extends Model
{
    use HasFactory;
    protected $table = 'bulk_discount';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
