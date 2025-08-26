<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondDbOrderShipment extends Model
{
    use HasFactory;
    protected $table = 'order_shipment';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
    
    public function shipmentOrder() {
        return $this->setConnection('mysql')->belongsTo(Order::class, 'order_id', 'id');
    }
    
    public function erpShipment() {
        // return $this->belongsTo(Order::class, 'order_id', 'id');
        return $this->belongsTo(ERPShowroomOrders::class, 'erp_order_no', 'order_no');
    }
    
    public function shipmentTicket() {
        return $this->setConnection('mysql')->belongsTo(InternalTicket::class, 'ticket_id', 'id');
    }
    
    public function shipmentDetailsData() {
        return $this->hasMany(SecondDbShipmentDetail::class, 'shipment_id');
        // return $this->hasMany(ShipmentDetail::class, 'shipment_id', 'id');
    }
    
    public function ImagesData() {
        return $this->hasMany(SecondDbShipmentImages::class, 'shipment_id', 'id');
    }
    
    public function statustimeline() {
        return $this->hasMany(SecondDbShipmentStatusTimeline::class, 'shipment_id', 'id');
    }
    
    public function comments() {
        return $this->hasMany(SecondDbShipmentComments::class, 'shipment_id', 'id');
    }
    
    public function warehouse() {
        return $this->setConnection('mysql')->belongsTo(Warehouse::class, 'warehouse', 'ln_code');
    }
    
    public function warehouseData() {
        return $this->setConnection('mysql')->belongsTo(Warehouse::class, 'warehouse', 'ln_code');
    }
    
    public function logisticData() {
        return $this->setConnection('mysql')->belongsTo(User::class, 'logistic_supervisor', 'employee_id');
    }
    
    public function riderData() {
        return $this->setConnection('mysql')->belongsTo(User::class, 'rider', 'employee_id');
    }
    
    public function shipmentSupervisorData() {
        return $this->setConnection('mysql')->belongsTo(User::class, 'waybill_creator_id', 'employee_id');
    }
    
    // Showroom Orders Relations
    public function ShippingAddressShowroomOrder() {
        return $this->belongsTo(SecondDbShippingAddressShowroomOrder::class, 'shipping_id', 'id');
    }
    
    public function SOdetails() {
        // return $this->hasMany(ShowroomOrderDetail::class, 'order_id', 'id');
        return $this->hasMany(SecondDbShowroomOrderDetail::class, 'order_id');
    }
    
    public function SOinvoices() {
        return $this->hasMany(SecondDbShowroomOrderInvoices::class, 'order_id', 'id');
    }
    
    public function showroomOrderUsersData() 
    {
        return $this->setConnection('mysql')->belongsToMany(User::class, 'showroomorder_users', 'showroomorder_id', 'user_id');
    }
    
    public function showroomOrderStoreData() 
    {
        return $this->setConnection('mysql')->belongsToMany(StoreLocator::class, 'showroomorder_stores', 'showroom_order_id', 'store_id');
    }
    
    public function shipmentShowroomOrder() {
        return $this->belongsTo(SecondDbOrderShipment::class, 'id', 'order_no');
    }
    
    public function shipmentshowroomDetailsData() {
        return $this->hasMany(SecondDbShowroomOrderDetail::class, 'order_id', 'id');
    }
    
    public function shipmentTransits() {
        return $this->hasMany(SecondDbOrderShipmentTransit::class, 'shipment_id', 'id');
    }
    
    public function activity() {
        return $this->hasMany(SecondDbShipmentActivity::class, 'shipment_id', 'id');
    }
    
    public function shipmentTagsData() 
    {
        return $this->belongsToMany(SecondDbShipmentTags::class, 'shipment_order_tags', 'shipment_id', 'shipment_tags_id');
    }
    
    public function shipmentRescheduleStatusData() {
        return $this->hasMany(SecondDbShipmentReschedule::class, 'shipment_id', 'id');
    }
}
