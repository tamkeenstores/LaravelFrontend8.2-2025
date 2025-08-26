<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalOrder extends Model
{
    use HasFactory;
    protected $table = 'internal_order';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function internalOrderDetail() {
        return $this->hasMany(InternalOrderDetails::class, 'order_id', 'id');
    }
}
