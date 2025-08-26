<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveredOrderDetail extends Model
{
    use HasFactory;
    protected $table = 'delivered_order_detail';
    protected $fillable = ['id', 'order_id', 'product_id', 'product_name', 'product_image', 'unit_price','quantity', 'total', 'enable_vat','expressproduct','fbt_id','gift_id','bogo_id'
  ,'created_at', 'updated_at'];
    
    public function orderData()
    {
        return $this->belongsTo(DeliveredOrder::class, 'order_id', 'id');
    }
    
    public function productData()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    public function productSku()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')->select('id','sku','is_bundle','ln_sku');
    }
    public function reviews() {
        return $this->hasMany(ProductReview::class, 'orderdetail_id', 'order_id')->where('status',1);
    }
}
