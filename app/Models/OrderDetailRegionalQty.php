<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetailRegionalQty extends Model
{
    use HasFactory;
    protected $table = 'order_detail_regional_qty';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function warehouseData(){
        return $this->belongsTo(Warehouse::class, 'warehouse_code', 'ln_code');
    }
    
     public function orderDetailData(){
        return $this->belongsTo(OrderDetail::class, 'id', 'order_detail_id');
    }
    
}
