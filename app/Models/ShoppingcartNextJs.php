<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingcartNextJs extends Model
{
    use HasFactory;
    protected $table = 'shoppingcart_nextjs';
    protected $guarded = ['created_at', 'updated_at'];
    protected $fillable = [
       'user_id', 'cartdata','firstemail','secondemail','thirdemail','fourthemail', 'affiliate_id'
    ];

    public function user(){
      return $this->belongsTo(User::class, 'user_id', 'id')->select(['id','firstname','lastname','phone','email']);
    }
}
