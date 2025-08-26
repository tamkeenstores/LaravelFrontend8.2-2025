<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FreeGift;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\States;
use App\Traits\CrudTrait;
use Carbon\Carbon;

class FreeGiftApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'free_gift';
    protected $relationKey = 'free_gift_id';


    public function model() {
        $data = ['limit' => -1, 'model' => FreeGift::class, 'sort' => ['id','asc']];
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
        return ['product_id' => 'products:id,name,name_arabic,sku','tag_id' => 'tags:id,tag_id,name,name_arabic', 'brand_id' => 'brands:id,name,name_arabic',
        'gift_list_id' => 'freegiftlist:id,product_id,free_gift_id,discount','productcategory_id' => 'productcategory:id,name,name_arabic'];
    }

    public function arrayData(){
        return ['include_cities' => 0, 'exclude_cities' => 0];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
         return ['category' => Productcategory::where('status','=',1)->orderby('id', 'DESC')->get(['id as value', 'name as label']),
         'tags' => SubTags::where('status','=',1)->orderby('id', 'DESC')->get(['id as value', 'name as label']),
         'brands' => Brand::where('status','=',1)->orderby('id', 'DESC')->get(['id as value', 'name as label']),
         'products' => Product::where('status','=',1)->orderby('id', 'DESC')->get(['id as value', 'sku as label']),
         'states' => States::where('country_id','191')->orderby('id', 'DESC')->get(['id as value', 'name as label']),
         ];
    }
    
    public function index(Request $request){
        $data = FreeGift::select('id', 'name', 'name_arabic', 'start_date', 'end_date', 'status'
        ,'notes')->orderBy('id', 'desc')->get();
        
        $freegift = FreeGift::where('status', 1)->where('end_date', '<=', Carbon::today()->toDateString())->get(['id', 'status', 'end_date']);
        
        foreach($freegift as $key => $value) {
            $value->status = 0;
            $value->update();
        }
        
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
