<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalTicketOrderDetails extends Model
{
    use HasFactory;
    protected $table = 'internal_ticket_order_details';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function ticketOrderDetailsData() {
        return $this->hasMany(OrderDetail::class, 'order_detail_id', 'id');
    }
}
