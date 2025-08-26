<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DiscountCoupon;
use App\Models\Productcategory;
// use App\Models\Product;
// use App\Models\Brand;
use App\Traits\CrudTrait;

class DiscountCouponApiController extends Controller
{
     use CrudTrait;
    protected $viewVariable = 'discount_coupon';
    protected $relationKey = 'discount_coupon';


    public function model() {
        $data = ['limit' => -1, 'model' => DiscountCoupon::class, 'sort' => ['id','asc']];
        return $data;
    }
    public function validationRules($resource_id = 0)
    {
        return [];
    }

    public function files(){
        return [];
    }

    public function relations(){
        return [];
    }

    public function arrayData(){
        return ['product_id' => 0, 'brand_id' => 0, 'product_category' => 0, 'offer_product' => 0, 'offer_brand' => 0, 'offer_category' => 0];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
        // return ['productcategories' => Productcategory::select(['id','name'])->where('status', 1)->get() , 'products' => Product::select(['id','sku'])->where('status', 1)->get(),
        // 'brands' => Brand::select(['id','name'])->where('status', 1)->get()];
    }
}
