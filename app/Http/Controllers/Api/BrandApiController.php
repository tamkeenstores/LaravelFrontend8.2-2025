<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Product;
use App\Traits\CrudTrait;
use DB;

class BrandApiController extends Controller
{
     use CrudTrait;
    protected $viewVariable = 'brands';
    protected $relationKey = 'brands_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Brand::class, 'sort' => ['id','desc']];
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
         return ['brand_media_image' => 'BrandMediaImage:id,image', 'brand_media_app_image' => 'BrandMediaAppImage:id,image', 'brand_category_id' => 'category'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
    }
    
    public function index(Request $request){
        $data = Brand::select('id', 'name', 'name_arabic', 'sorting', 'brand_image_media', 'brand_app_image_media'
        ,'clicks', 'status')->with('BrandMediaImage:id,image', 'BrandMediaAppImage:id,image')
        ->withCount('productname')->orderBy('id', 'desc')->get();
        
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
    
    public function destroy($id)
    {
        $brands = Brand::with('BrandMediaImage', 'BrandMediaAppImage')->findorFail($id);
        $brands->delete();
        return response()->json(['success' => true, 'message' =>'Brand Has been deleted!']);
    }
    
    public function multidelete(Request $request) {
        $success = false;
        if(isset($request->id)) {
            $ids = $request->id;
            $deletetags = Brand::with('BrandMediaImage', 'BrandMediaAppImage')->whereIn('id',$ids)->get();
            $deletetags->each->delete();
            $success = true;
        }
        return response()->json(['success' => $success, 'message' => 'Selected Brands Has been deleted!']);
            
    }
    
    public function Countsdatafor() {
        
        $brand = Brand::with('productname')->get();
        $brandproduct = Brand::with(['productname' => function ($query) {
            $query;
        }])
            ->withCount('productname')
			->orderBy('productname_count', 'desc')
            ->first();
        $lowestbrandproduct = Brand::with(['productname' => function ($query) {
            $query;
        }])
            ->withCount('productname')
			->orderBy('productname_count', 'asc')
            ->first();
            
        $totalproductcount = Brand::with(['productname' => function ($query) {
            $query;
        }])
            ->withCount('productname')->get();
        // print_r($brandproduct);die();
        $brandcount = $brand->count();
        $brandenable = Brand::where('status', 1)->get();
        $enabled = $brandenable->count();
        
        $sellingbrand = Brand::
        select('brands.name as selling_name', DB::raw('ROUND(sum(order_detail.total)) as selling_price'))
        // ->where('total', '>', 1)
        ->groupBy('brands.id')
        ->leftJoin('products as sellingpro', function($join) {
            $join->on('brands.id', '=', 'sellingpro.brands');
        })
        ->leftJoin('order_detail', function($join) {
            $join->on('sellingpro.id', '=', 'order_detail.product_id');
        })
        ->where('brands.status', 1)
        ->orderBy('selling_price', 'DESC')
        ->first();
        
        // print_r($lowestbrandproduct);die();
        
        
        return response()->json(['count' => $brandcount, 'brand' => $brand, 'enablebrands' => $enabled, 'brandproduct' => $brandproduct,
        'lowestbrandproduct' => $lowestbrandproduct, 'totalproductcount' => $totalproductcount, 'highestsellingbrand' => $sellingbrand]);
    }
}
