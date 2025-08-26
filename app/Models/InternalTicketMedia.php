<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalTicketMedia extends Model
{
    use HasFactory;
    protected $table = 'internal_ticket_media';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
