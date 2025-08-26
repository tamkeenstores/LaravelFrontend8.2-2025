<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HasColumnMany\UseColumnMany;

class RulesRestriction extends Model
{
    use HasFactory;
    use UseColumnMany;
    protected $table = 'rule_restriction';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function brandsData(){
        return $this->hasColumnMany(Brand::class, 'brand_id');
    }
    
    public function categoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'category_id');
    }
    
    public function subtagsData(){
        return $this->hasColumnMany(SubTags::class, 'sub_tag_id');
    }
    
    public function productData(){
        return $this->hasColumnMany(Product::class, 'product_id');
    }
    
}
