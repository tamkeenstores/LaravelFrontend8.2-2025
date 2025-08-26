<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketTag extends Model
{
    use HasFactory;
    protected $table = 'ticket_tag';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
