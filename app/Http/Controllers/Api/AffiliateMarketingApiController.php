<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AffiliateMarketing;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\Brand;
use App\Models\States;
use App\Traits\CrudTrait;

class AffiliateMarketingApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'affiliate_marketing';
    protected $relationKey = 'affiliate_marketing_id';


    public function model() {
        $data = ['limit' => -1, 'model' => AffiliateMarketing::class, 'sort' => ['id','asc']];
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
        return ['brand_id' => 'brands:id,name,name_arabic', 'productcategory_id' => 'productcategory:id,name,name_arabic',
        'product_id' => 'products:id,name,name_arabic,sku', 'city_id' => 'city'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
         return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
         'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
         'products' => Product::where('status','=',1)->get(['id as value', 'sku as label']),
         'states' => States::where('country_id','191')->get(['id as value', 'name as label']),
         ];
    }
}
