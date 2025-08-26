<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderShipment extends Model
{
    use HasFactory;
    protected $table = 'order_shipment';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function shipmentOrder() {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    
    public function shipmentTicket() {
        return $this->belongsTo(InternalTicket::class, 'ticket_id', 'id');
    }
    
    public function shipmentDetailsData() {
        return $this->hasMany(ShipmentDetail::class, 'shipment_id', 'id');
    }
    
    public function ImagesData() {
        return $this->hasMany(ShipmentImages::class, 'shipment_id', 'id');
    }
    
    public function statustimeline() {
        return $this->hasMany(ShipmentStatusTimeline::class, 'shipment_id', 'id');
    }
    
    public function comments() {
        return $this->hasMany(ShipmentComments::class, 'shipment_id', 'id');
    }
    
    public function warehouse() {
        return $this->belongsTo(Warehouse::class, 'warehouse', 'id');
    }
    
    public function logisticData() {
        return $this->belongsTo(User::class, 'logistic_supervisor', 'id');
    }
    
    public function riderData() {
        return $this->belongsTo(User::class, 'rider', 'id');
    }
    
    public function shipmentSupervisorData() {
        return $this->belongsTo(User::class, 'waybill_creator_id', 'id');
    }
    
    // Showroom Orders Relations
    public function ShippingAddressShowroomOrder() {
        return $this->belongsTo(ShippingAddressShowroomOrder::class, 'shipping_id', 'id');
    }
    
    public function SOdetails() {
        return $this->hasMany(ShowroomOrderDetail::class, 'order_id', 'id');
    }
    
    public function SOinvoices() {
        return $this->hasMany(ShowroomOrderInvoices::class, 'order_id', 'id');
    }
    
    public function showroomOrderUsersData() 
    {
        return $this->belongsToMany(User::class, 'showroomorder_users', 'showroomorder_id', 'user_id');
    }
    
    public function showroomOrderStoreData() 
    {
        return $this->belongsToMany(StoreLocator::class, 'showroomorder_stores', 'showroom_order_id', 'store_id');
    }
    
    public function shipmentShowroomOrder() {
        return $this->belongsTo(OrderShipment::class, 'id', 'order_no');
    }
    
    public function shipmentshowroomDetailsData() {
        return $this->hasMany(ShowroomOrderDetail::class, 'order_id', 'id');
    }
    
    // public function shipmentshowroomDetailsData() {
    //     return $this->hasMany(ShipmentDetail::class, 'shipment_id', 'id');
    // }
    
    public function shipmentTransits() {
        return $this->hasMany(OrderShipmentTransit::class, 'shipment_id', 'id');
    }
    
    
    public function activity() {
        return $this->hasMany(ShipmentActivity::class, 'shipment_id', 'id');
    }
}
