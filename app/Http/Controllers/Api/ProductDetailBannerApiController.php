<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductDetailBanner;
// use App\Models\Media;
use App\Models\Productcategory;
use App\Traits\CrudTrait;

class ProductDetailBannerApiController extends Controller
{
     use CrudTrait;
    protected $viewVariable = 'product_detail_banner';
    protected $relationKey = 'product_detail_banner_id';


    public function model() {
        $data = ['limit' => -1, 'model' => ProductDetailBanner::class, 'sort' => ['id','asc']];
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
        return ['product_id' => 'ProductDetailBannerproducts', 'category_id' => 'ProductDetailBannercategories'];
        // return ['product_id' => 'ProductDetailBannerproducts.products', 'category_id' => 'ProductDetailBannercategories.Category', 'image_en_media' => 'ImageEnMedia', 'image_ar_media' => 'ImageArMedia'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
        // return ['category' => Productcategory::get(['id', 'name', 'name_arabic', 'slug']), 'product' => Product::get(['id', 'name', 'name_arabic', 'sku', 'slug'])];
    }
}
