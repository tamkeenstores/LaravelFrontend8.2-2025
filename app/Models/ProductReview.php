<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use HasFactory;
    protected $table = 'product_review';
    protected $fillable = ['id', 'orderdetail_id', 'product_sku', 'rating', 'title', 'review','user_id', 'anonymous', 'status','created_at', 'updated_at'];
    // protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function UserData() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function OrderDetailData() {
        return $this->belongsTo(OrderDetail::class, 'orderdetail_id', 'id');
    }
}
