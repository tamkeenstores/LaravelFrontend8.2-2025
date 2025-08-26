<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TamkeenPremium extends Model
{
    use HasFactory;
    protected $table = 'tamkeen_premium';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function memberships() {
        return $this->hasMany(PremiumMembership::class, 'premium_id', 'id');
    }
    
    public function featuredImage(){
        return $this->belongsTo(ProductMedia::class, 'feature_image', 'id');
    }
    
}
