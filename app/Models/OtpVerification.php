<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    use HasFactory;
    protected $table = 'otp_verification';

    protected $guarded = ['id', 'created_at', 'updated_at'];
}
