<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InputChannel extends Model
{
    use HasFactory;
    protected $table = 'input_channels';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
}
