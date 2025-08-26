<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PromotionBanner;
use App\Models\Productcategory;
//  use App\Models\Product;
use App\Traits\CrudTrait;

class PromotionBannerApiController extends Controller
{
     use CrudTrait;
    protected $viewVariable = 'promotion_banner';
    protected $relationKey = 'promotion_banner_id';


    public function model() {
        $data = ['limit' => -1, 'model' => PromotionBanner::class, 'sort' => ['id','asc']];
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
        return ['promotion_banner_id' => 'PromotionBannerproducts', 'promotion_banner_id' => 'PromotionBannercategories'];
        // , 'image_media' => 'ImageMedia'
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
        // return ['promotionBanners_categories' => Productcategory::where('status', 1)->get(['id', 'name', 'name_arabic']),
        // 'promotionBanners_products' => Product::where('status', 1)->get(['id', 'name', 'sku', 'name_arabic'])];
    }
}
