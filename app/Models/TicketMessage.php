<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    use HasFactory;
    protected $table ='ticket_message';
    protected $guarded =['id','created_at','updated_at'];
    
    public function customerData()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    // public function DocumentData() {
    //     return $this->hasMany(TicketDocument::class, 'ticket_message_id', 'id');
    // }
    // public function DocsData() {
    //     return $this->hasMany(TicketDocument::class, 'ticket_message_id', 'id')->select(['document']);
    // }
}
