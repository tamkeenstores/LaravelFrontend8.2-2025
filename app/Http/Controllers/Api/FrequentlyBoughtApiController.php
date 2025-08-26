<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FrequentlyBoughtTogether;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\States;
use App\Traits\CrudTrait;

class FrequentlyBoughtApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'fbt';
    protected $relationKey = 'fbt_id';


    public function model() {
        $data = ['limit' => -1, 'model' => FrequentlyBoughtTogether::class, 'sort' => ['id','desc']];
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
        return ['product_id' => 'products:id,name,sku','tag_id' => 'tags:id,tag_id,name', 'brand_id' => 'brands:id,name',
        'fbt_list_id' => 'fbtlist:id,fbt_id,discount,product_id', 
        'productcategory_id' => 'productcategory:id,name'];
    }

    public function arrayData(){
        return ['include_cities' => 0, 'exclude_cities' => 0];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
         return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
         'tags' => SubTags::where('status','=',1)->get(['id as value', 'name as label']),
         'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
         'products' => Product::where('status','=',1)->get(['id as value', 'sku as label']),
         'states' => States::where('country_id','191')->get(['id as value', 'name as label']),
         ];
    }
}
