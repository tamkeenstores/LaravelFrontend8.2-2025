<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserComissions extends Model
{
    use HasFactory;
    protected $table = 'user_comissions';
    protected $fillable = ['id',  'user_id','affiliation_id', 'title', 'title_arabic', 'disable_type','rules_type','rules_id','coupon_id', 'slug_code', 'value', 'calculate_type', 'order_no','notes','status'];

    public function UserDetail()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function RulesData()
    {
        return $this->belongsTo(Rules::class, 'rules_id', 'id');
    }
    
    public function CouponData()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }
    
    public function AffiliationData()
    {
        return $this->belongsTo(Affiliation::class, 'affiliation_id', 'id');
    }
    
    public function orderData()
    {
        return $this->belongsTo(Order::class, 'order_no', 'order_no');
    }
}
