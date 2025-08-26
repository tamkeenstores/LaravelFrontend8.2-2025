<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSliders;
use App\Models\Productcategory;
use App\Models\Product;
use App\Models\Brand;
use App\Models\SubTags;
use App\Traits\CrudTrait;

class AppSlidersApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'appSlider';
    protected $relationKey = 'app_slider_id';
    
    public function model() {
        $data = ['limit' => -1, 'model' => AppSliders::class, 'sort' => ['sorting','desc']];
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
        // return ['imageen' => 'ImageEn:id,image', 'imagear' => 'ImageAr:id,image'];
        return ['product_id' => 'ProductData:id,name,sku', 'brand_id' => 'BrandData:id,name', 'category_id' => 'CategoryData:id,name', 'tag_id' => 'TagData:id,name', 'imageen' => 'ImageEn:id,image', 'imagear' => 'ImageAr:id,image'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [
            'category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
            'products' => Product::where('status','=',1)->get(['id as value', 'sku as label']),
            'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
            'tags' => SubTags::where('status','=',1)->get(['id as value', 'name as label']),
        ];
    }
}
