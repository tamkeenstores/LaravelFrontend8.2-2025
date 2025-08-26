<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalTicket extends Model
{
    use HasFactory;
    protected $table = 'internal_ticket';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function departmentData() {
        return $this->belongsTo(Department::class, 'department', 'id');
    }
    
    public function sectionData() {
        return $this->belongsTo(Section::class, 'section', 'id');
    }
    
    public function typeData() {
        return $this->belongsTo(companyTypes::class, 'type', 'id');
    }
    
    public function SLAData() {
        return $this->belongsTo(SLA::class, 'sla', 'id');
    }
    
    public function customerData() {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
    
    public function userData() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function assigneeData() {
        return $this->belongsTo(User::class, 'assignee', 'id');
    }
    
    public function channelData() {
        return $this->belongsTo(InputChannel::class, 'input_channel', 'id');
    }
    
    public function orderData() {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    
    public function internalOrderData() {
        return $this->belongsTo(InternalOrder::class, 'order_id', 'id');
    }
    
    public function internalTicketOrderDetailData() {
        return $this->hasMany(InternalTicketOrderDetails::class, 'ticket_id', 'id');
    }
    
    public function internalTicketCommentsData() {
        return $this->hasMany(InternalTicketComments::class, 'ticket_id', 'id');
    }
    
    public function internalTicketHistoryData() {
        return $this->hasMany(InternalTicketHistory::class, 'ticket_id', 'id');
    }
    
    public function ticketTagData() {
        return $this->hasMany(TicketTag::class, 'ticket_id', 'id');
    }
    
    public function ticketMediaData() {
        return $this->hasMany(InternalTicketMedia::class, 'ticket_id', 'id');
    }
    
    public function tagsData() {
        return $this->belongsToMany(companyTags::class, 'ticket_tag', 'ticket_id', 'tag_id');
    }
    
    public function frontTicketProducts() {
        return $this->belongsTo(Product::class, 'device_model', 'id');
    }
    public function frontTicketCategory() {
        return $this->belongsTo(Productcategory::class, 'device_category', 'id');
    }
    
    public function showroomData() {
        return $this->belongsTo(StoreLocator::class, 'requestor_showroom', 'id');
    }
    
    public function warehouseData() {
        return $this->belongsTo(Warehouse::class, 'requestor_warehouse', 'id');
    }
}
