<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertMessage extends Model
{
    use HasFactory;
    protected $table = 'alert_message';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
