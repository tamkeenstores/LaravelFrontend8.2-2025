<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalTicketComments extends Model
{
    use HasFactory;
    protected $table = 'internal_ticket_comments';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function userData() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
