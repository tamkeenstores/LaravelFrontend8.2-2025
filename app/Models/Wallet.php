<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HasColumnMany\UseColumnMany;

class Wallet extends Model
{
    use HasFactory;
    use UseColumnMany;
    protected $table = 'wallet';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function restrictions() {
        return $this->hasMany(WalletRestriction::class, 'wallet_id', 'id');
    }
    
    public function conditions() {
        return $this->hasMany(RulesConditions::class, 'rule_id', 'id')->where('module_type', 5);
    }
    
    public function brandsData(){
        return $this->hasColumnMany(Brand::class, 'restriction_brand_id');
    }
    
    public function categoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'restriction_category_id');
    }
    
    public function subtagsData(){
        return $this->hasColumnMany(SubTags::class, 'restriction_sub_tag_id');
    }
    
    public function productData(){
        return $this->hasColumnMany(Product::class, 'restriction_product_id');
    }
    
    
    public function appliedbrandsData(){
        return $this->hasColumnMany(Brand::class, 'applied_brand_id');
    }
    
    public function appliedcategoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'applied_category_id');
    }
    
    public function appliedsubtagsData(){
        return $this->hasColumnMany(SubTags::class, 'applied_sub_tag_id');
    }
    
    public function appliedproductData(){
        return $this->hasColumnMany(Product::class, 'applied_product_id');
    }
}
