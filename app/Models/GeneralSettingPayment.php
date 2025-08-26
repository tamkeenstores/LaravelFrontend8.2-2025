<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HasColumnMany\UseColumnMany;

class GeneralSettingPayment extends Model
{
    use HasFactory;
    use UseColumnMany;
    protected $table = 'general_setting_payment';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function hyperpaybrandsData(){
        return $this->hasColumnMany(Brand::class, 'hyperpay_brand_id');
    }
    
    public function hyperpaycategoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'hyperpay_category_id');
    }
    
    public function hyperpaysubtagsData(){
        return $this->hasColumnMany(SubTags::class, 'hyperpay_sub_tag_id');
    }
    
    public function hyperpayproductData(){
        return $this->hasColumnMany(Product::class, 'hyperpay_product_id');
    }
    
    
    public function applepaybrandsData(){
        return $this->hasColumnMany(Brand::class, 'applepay_brand_id');
    }
    
    public function applepaycategoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'applepay_category_id');
    }
    
    public function applepaysubtagsData(){
        return $this->hasColumnMany(SubTags::class, 'applepay_sub_tag_id');
    }
    
    public function applepayproductData(){
        return $this->hasColumnMany(Product::class, 'applepay_product_id');
    }
    
    public function tasheelbrandsData(){
        return $this->hasColumnMany(Brand::class, 'tasheel_brand_id');
    }
    
    public function tasheelcategoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'tasheel_category_id');
    }
    
    public function tasheelsubtagsData(){
        return $this->hasColumnMany(SubTags::class, 'tasheel_sub_tag_id');
    }
    
    public function tasheelproductData(){
        return $this->hasColumnMany(Product::class, 'tasheel_product_id');
    }
    
    public function tabbybrandsData(){
        return $this->hasColumnMany(Brand::class, 'tabby_brand_id');
    }
    
    public function tabbycategoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'tabby_category_id');
    }
    
    public function tabbysubtagsData(){
        return $this->hasColumnMany(SubTags::class, 'tabby_sub_tag_id');
    }
    
    public function tabbyproductData(){
        return $this->hasColumnMany(Product::class, 'tabby_product_id');
    }
    
    public function tamarabrandsData(){
        return $this->hasColumnMany(Brand::class, 'tamara_brand_id');
    }
    
    public function tamaracategoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'tamara_category_id');
    }
    
    public function tamarasubtagsData(){
        return $this->hasColumnMany(SubTags::class, 'tamara_sub_tag_id');
    }
    
    public function tamaraproductData(){
        return $this->hasColumnMany(Product::class, 'tamara_product_id');
    }
    
    
    public function codbrandsData(){
        return $this->hasColumnMany(Brand::class, 'cod_brand_id');
    }
    
    public function codcategoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'cod_category_id');
    }
    
    public function codsubtagsData(){
        return $this->hasColumnMany(SubTags::class, 'cod_sub_tag_id');
    }
    
    public function codproductData(){
        return $this->hasColumnMany(Product::class, 'cod_product_id');
    }
    
    public function codcityData(){
        return $this->hasColumnMany(States::class, 'cod_city_id');
    }
    
    public function madfubrandsData(){
        return $this->hasColumnMany(Brand::class, 'madfu_brand_id');
    }
    
    public function madfucategoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'madfu_category_id');
    }
    
    public function madfusubtagsData(){
        return $this->hasColumnMany(SubTags::class, 'madfu_sub_tag_id');
    }
    
    public function madfuproductData(){
        return $this->hasColumnMany(Product::class, 'madfu_product_id');
    }
    
    public function mispaybrandsData(){
        return $this->hasColumnMany(Brand::class, 'mispay_brand_id');
    }
    
    public function mispaycategoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'mispay_category_id');
    }
    
    public function mispaysubtagsData(){
        return $this->hasColumnMany(SubTags::class, 'mispay_sub_tag_id');
    }
    
    public function mispayproductData(){
        return $this->hasColumnMany(Product::class, 'mispay_product_id');
    }
    //clickpay
    public function clickpaybrandsData(){
        return $this->hasColumnMany(Brand::class, 'clickpay_brand_id');
    }
    
    public function clickpaycategoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'clickpay_category_id');
    }
    
    public function clickpaysubtagsData(){
        return $this->hasColumnMany(SubTags::class, 'clickpay_sub_tag_id');
    }
    
    public function clickpayproductData(){
        return $this->hasColumnMany(Product::class, 'clickpay_product_id');
    }
     //clickpay(applepay)
    public function clickpayApplepaybrandsData(){
        return $this->hasColumnMany(Brand::class, 'clickpay_applepay_brand_id');
    }
    
    public function clickpayApplepaycategoriesData(){
        return $this->hasColumnMany(Productcategory::class, 'clickpay_applepay_category_id');
    }
    
    public function clickpayApplepaysubtagsData(){
        return $this->hasColumnMany(SubTags::class, 'clickpay_applepay_sub_tag_id');
    }
    
    public function clickApplepayproductData(){
        return $this->hasColumnMany(Product::class, 'clickpay_applepay_product_id');
    }
    
}
