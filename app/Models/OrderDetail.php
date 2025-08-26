<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;
    protected $table = 'order_detail';
    protected $fillable = ['id', 'order_id', 'product_id', 'product_name', 'product_image', 'unit_price','quantity', 'total', 'enable_vat','expressproduct', 'express_qty','fbt_id','gift_id','bogo_id'
  ,'is_video_add','created_at', 'updated_at'];
    
    // protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function orderData()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
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
    
    public function OrderDetailRegionalQty() {
        return $this->hasMany(OrderDetailRegionalQty::class, 'order_detail_id', 'id');
    }
    
    public function ugcOrderData()
    {
        return $this->belongsTo(UserGeneratedContent::class, 'id', 'order_detail_id');
    }
}
