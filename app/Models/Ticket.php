<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $table ='tickets';
    protected $guarded =['id','created_at','updated_at'];
    
    public function customerData()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select(['id','firstname','firstname','lastname','phone','email']);
    }
    
    // public function message() {
    //     return $this->hasMany(TicketMessage::class, 'ticket_no', 'ticket_no')->orderBy('created_at', 'DESC')->select(['id','user_id','ticket_no','description','department','subject', 'priority', 'status']);
    // }
}
