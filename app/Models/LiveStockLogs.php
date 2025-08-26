<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStockLogs extends Model
{
    use HasFactory;
    protected $table = 'livestock_logs';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
}
