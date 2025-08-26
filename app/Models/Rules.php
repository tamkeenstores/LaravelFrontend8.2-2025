<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rules extends Model
{
    use HasFactory;
    protected $table = 'discount_rules';
    protected $guarded = ['id', 'created_at', 'updated_at'];
   
    public function restrictions() {
        return $this->hasMany(RulesRestriction::class, 'rule_id', 'id');
    }
    
    public function conditions() {
        return $this->hasMany(RulesConditions::class, 'rule_id', 'id')->where('module_type', 0);
    }
    
    public function bogodiscount() {
        return $this->hasMany(BogoDiscount::class, 'rule_id', 'id');
    }
    
    public function bulkdiscount() {
        return $this->hasMany(BulkDiscount::class, 'rule_id', 'id');
    }
    
    // public function discount() {
    //     return $this->hasMany(RulesDiscount::class, 'rule_id', 'id');
    // }
   
}
