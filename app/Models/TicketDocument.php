<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketDocument extends Model
{
    use HasFactory;
    protected $table ='ticket_document';
    protected $guarded =['id','created_at','updated_at'];
}
