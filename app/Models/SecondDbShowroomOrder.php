<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondDbShowroomOrder extends Model
{
    use HasFactory;
    protected $table = 'showroom_order';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
    
    public function details() {
        return $this->hasMany(SecondDbShowroomOrderDetail::class, 'order_id', 'id');
    }
    
    public function comments() {
        return $this->hasMany(SecondDbShipmentComments::class, 'order_id', 'id');
    }
    
    public function invoices() {
        return $this->hasMany(SecondDbShowroomOrderInvoices::class, 'order_id', 'id');
    }
    
    public function statustimeline() {
        return $this->hasMany(SecondDbShipmentStatusTimeline::class, 'order_id', 'id');
    }
    
    public function Address() {
        return $this->belongsTo(SecondDbShippingAddressShowroomOrder::class, 'shipping_id', 'id');
    }
    
    public function riderData() {
        return $this->setConnection('mysql')->belongsTo(User::class, 'rider', 'employee_id');
    }
    
    public function showroomOrderUsersData() 
    {
        return $this->setConnection('mysql')->belongsToMany(User::class, 'showroomorder_users', 'showroomorder_id', 'user_id');
    }
    
    public function showroomOrderStoreData() 
    {
        return $this->setConnection('mysql')->belongsToMany(StoreLocator::class, 'showroomorder_stores', 'showroom_order_id', 'store_id');
    }
    
}
