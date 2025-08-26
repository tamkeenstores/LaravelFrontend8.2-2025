<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlatRateClass extends Model
{
    use HasFactory;
    protected $table = 'flat_rate_class';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function shipClass() {
        return $this->hasMany(ShippingClasses::class, 'id', 'class_id');
    }
}
