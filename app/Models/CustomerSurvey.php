<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSurvey extends Model
{
    use HasFactory;
    protected $table = 'customer_survey';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    // public function orderData(){
    //     return $this->belongsTo(Order::class, 'order_id', 'id');
    // }
    public function showroomData(){
        return $this->belongsTo(StoreLocator::class, 'showroom_id', 'id');
    }
    // public function customerData(){
    //     return $this->belongsTo(User::class, 'customer_id', 'id');
    // }
}
