<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HasColumnMany\UseColumnMany;

class Affiliation extends Model
{
    use HasFactory;
    use UseColumnMany;
    protected $table = 'affiliation';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function UsersData() {
        return $this->hasColumnMany(User::class, 'specific_users_id', 'phone_number');
    }
    
    public function restrictions() {
        return $this->hasMany(AffiliationRestriction::class, 'affiliation_id', 'id');
    }
    
    public function conditions() {
        return $this->hasMany(RulesConditions::class, 'rule_id', 'id')->where('module_type', 4);
    }
    
    public function RedirectCat() {
        return $this->belongsTo(Productcategory::class, 'category_id', 'id');
    }
    public function RedirectBrand() {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
    public function RedirectPro() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    public function RedirectTag() {
        return $this->belongsTo(SubTags::class, 'sub_tag_id', 'id');
    }
    
    public function rulesData(){
        return $this->hasColumnMany(Rules::class, 'rules_id');
    }
    
    public function couponData(){
        return $this->hasColumnMany(Coupon::class, 'coupon_id');
    }
}
