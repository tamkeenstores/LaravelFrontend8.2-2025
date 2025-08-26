<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralEmailTimes extends Model
{
    use HasFactory;
    protected $table = 'general_email_times';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
