<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationToken extends Model
{
    use HasFactory;
    protected $table = 'notification_token';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
