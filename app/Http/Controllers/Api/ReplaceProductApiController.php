<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReplaceProduct;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Traits\CrudTrait;

class ReplaceProductApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'replace_product';
    protected $relationKey = 'replace_product_id';


    public function model() {
        $data = ['limit' => -1, 'model' => ReplaceProduct::class, 'sort' => ['id','asc']];
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
        return ['productcategory_id' => 'productcategory:id,name', 'product_id' => 'products:id,name,sku',
        'brand_id' => 'brand:id,name', 'subtag_id' => 'subtag:id,name'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
         return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
         'tags' => SubTags::where('status','=',1)->get(['id as value', 'name as label']),
         'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
         'products' => Product::where('status','=',1)->get(['id as value', 'sku as label']),
         ];
    }
    
    public function index(Request $request){
        $data = ReplaceProduct::select('id', 'name', 'name_arabic', 'description', 'status')->get();
        
        $response = [
            'data' => $data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
}
