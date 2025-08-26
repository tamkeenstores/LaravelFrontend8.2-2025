<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Productcategory;
use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\OrderDetail;
use App\Models\SubTags;
use App\Traits\CrudTrait;
use DB;

class ProductCategoryApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'productcategories';
    protected $relationKey = 'productcategories_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Productcategory::class, 'sort' => ['id','asc']];
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
        return ['parent_id' => 'category:id,name,name_arabic',
        'parent_id_child' => 'child:id,name,parent_id','web_media_image' => 'WebMediaImage:id,image','mobile_media_image' => 'MobileMediaAppImage:id,image',
        'filter_cat_id' => 'filtercategory', 'feature_image' => 'FeatureImage'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['category' => Productcategory::get(['id as value', 'name as label']),
        'tags' => SubTags::where('status','=',1)->get(['id as value', 'name as label']),
        'products' => Product::where('status','=',1)->get(['id as value', 'name as label']),
        ];
        // return ['badge_categories' => Productcategory::get(['id', 'name', 'name_arabic']), 'badge_product' => Product::get(['id', 'name', 'sku', 'name_arabic'])];
    }
    
    public function index(Request $request){
        $data = Productcategory::select('id', 'name', 'slug', 'sort', 'web_image_media', 'mobile_image_media'
        ,'clicks', 'menu', 'status')->with('WebMediaImage:id,image', 'MobileMediaAppImage:id,image')
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
    
    public function catdata() {
        $cat = Productcategory::get();
        $catcount = $cat->count();
        $catenable = Productcategory::where('status', 1)->get();
        $enabled = $catenable->count();
        $maxviews = $cat->max('views');
        $maxdata = Productcategory::where('views', $maxviews)->get('name');
        $minviews = $cat->min('views');
        $mindata = Productcategory::where('views', $minviews)->get('name');
        
        $sellingcategory = OrderDetail::
        select('productcategories.name as selling_cat', DB::raw('ROUND(sum(sellingpro.sale_price)) as selling_price'))
        ->where('total', '>', 1)
        ->groupBy('productcategories.id')
        ->leftJoin('products as sellingpro', function($join) {
            $join->on('order_detail.product_id', '=', 'sellingpro.id');
        })
        ->leftJoin('product_categories', function($join) {
            $join->on('sellingpro.id', '=', 'product_categories.product_id');
        })
        ->leftJoin('productcategories', function($join) {
            $join->on('product_categories.category_id', '=', 'productcategories.id');
        })
        ->where('productcategories.menu', 1)
        ->where('productcategories.status', 1)
        ->orderByRaw('COUNT(*) DESC')
        ->first();
        
        
        return response()->json(['count' => $catcount, 'cat' => $cat, 'enablebrands' => $enabled, 'maxviews' => $maxviews, 'maxdata' =>$maxdata , 'minviews' => $minviews, 'mindata' =>$mindata
        ,'highestsellingcategory' => $sellingcategory]);
    }
    
    // public function productcount() {
    //     $product = Product::get();
    //     $catid = CategoryProduct:: where('product_id', $product->id)->get();
    //     print_r($catid);
    // }
}
