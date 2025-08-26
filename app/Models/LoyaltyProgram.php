<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyProgram extends Model
{
    use HasFactory;
    protected $table = 'loyalty_program';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function restrictions() {
        return $this->hasMany(LoyaltyRestrictions::class, 'loyalty_id', 'id');
    }
    
    public function conditions() {
        return $this->hasMany(RulesConditions::class, 'rule_id', 'id')->where('module_type', 3);
    }
}
