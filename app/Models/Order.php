<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'order';
    protected $fillable = ['id', 'order_no', 'customer_id', 'shipping_id', 'status', 'paymentmethod','paymentid', 'shippingMethod','affiliationcode', 'discountallowed','giftvoucherallowed', 'loyaltyFreeShipping',
        'note',
        'lang',
        'erp_status',
        'madac_id',
        'userDevice','erp_status','erp_fetch_date','erp_fetch_time','pendingemailstatus','pending_order_date' ,'token','mobileapp','deleted_at','created_at', 'main_date', 'updated_at',
        'waybill_type',
        'naqeel_waybill',
        'samsa_waybill',
        'aramex_waybill',
        'logestechs_waybill',
        'starlink_waybill',
        'shipa_waybill',
        'flow_waybill',
        'pending_email_count',
        'pending_whatsapp_count',
        'preferred_shift',
        'preferred_date',
        'accept_by',
        'assigned_rider',
        'invoice_code',
        'madfu_preference',
        'order_type',
        'store_id',
        'otp_code',
        'loyalty_shipping',
        'delivery_date'
        ];
        
    // protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function UserDetail()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
    
    public function details() {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }
    
    public function comments() {
        return $this->hasMany(OrderComments::class, 'order_id', 'id');
    }
    
    public function statustimeline() {
        return $this->hasMany(OrderStatusTimeLine::class, 'order_id', 'id');
    }
    
    public function ordersummary() {
        return $this->hasMany(OrderSummary::class, 'order_id', 'id');
    }
    
    public function orderloyaltypoints() {
        return $this->hasMany(LoyaltyHistory::class, 'order_id', 'id');
    }
    
    public function usercommission() {
        return $this->hasMany(UserComissions::class, 'order_no', 'order_no');
    }
    
    public function warehouse() {
        return $this->belongsTo(Warehouse::class, 'store_id', 'id');
    }
    
    public function storeData(){
        return $this->belongsTo(Warehouse::class, 'store_id', 'id');
    }
    
    public function showroomData(){
        return $this->belongsTo(StoreLocator::class, 'store_id', 'id');
    }
    
    public function Address()
    {
        return $this->belongsTo(shippingAddress::class, 'shipping_id', 'id')->withTrashed();
    }
    
    public function freegifts() 
    {
        return $this->belongsToMany(FreeGift::class, 'order_free_gift', 'order_id', 'free_gift_id');
    }
    
    public function fbt() 
    {
        return $this->belongsToMany(FrequentlyBoughtTogether::class, 'order_fbt', 'order_id', 'fbt_id');
    }
    
    public function bogo() 
    {
        return $this->belongsToMany(FreeGift::class, 'order_bogo', 'order_id', 'bogo_id');
    }
    
    public function shipmentOrder() {
        return $this->hasOne(OrderShipment::class, 'order_id', 'id');
    }
}
