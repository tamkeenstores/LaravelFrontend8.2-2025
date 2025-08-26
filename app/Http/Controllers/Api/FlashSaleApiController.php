<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\FlashRestriction;
use App\Traits\CrudTrait;

class FlashSaleApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'flash_sale';
    protected $relationKey = 'flash_sale_id';


    public function model() {
        $data = ['limit' => -1, 'model' => FlashSale::class, 'sort' => ['id','asc']];
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
        return ['restrictions_id' => 'restrictions', 'featuredImage_id' => 'featuredImage:id,image', 'featuredImageApp_id' => 'featuredImageApp:id,image'];
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
        $data = FlashSale::select('id', 'name', 'name_arabic', 'start_date', 'end_date', 'discount_amount'
        ,'quantity', 'left_quantity', 'image', 'image_app', 'status')->with('featuredImage:id,image', 'featuredImageApp:id,image')
        ->orderBy('id', 'desc')
        ->get();
        
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
    
    public function store(Request $request) 
    {
        $falshsale = FlashSale::create([
            'name' => $request->get('name'),
            'name_arabic' => $request->get('name_arabic'),
            'notes' => isset($request->notes) ? $request->notes : null,
            'start_date' => isset($request->start_date) ? $request->start_date : null,
            'end_date' => isset($request->end_date) ? $request->end_date : null,
            'status' => isset($request->status) ? $request->status : 0,
            'quantity' => isset($request->quantity) ? $request->quantity : null,
            'left_quantity' => isset($request->quantity) ? $request->quantity : null,
            'discount_type' => isset($request->discount_type['value']) ? $request->discount_type['value'] : null,
            'discount_amount' => isset($request->discount_amount) ? $request->discount_amount : null,
            'image' => isset($request->image) ? $request->image : null,
            'image_app' => isset($request->image_app) ? $request->image_app : null,
            'redirection_type' => isset($request->redirection_type) ? $request->redirection_type : null,
            'redirection_products' => isset($request->redirection_products) ? $request->redirection_products : null,
            'redirection_categories' => isset($request->redirection_categories) ? $request->redirection_categories : null,
            'redirection_brands' => isset($request->redirection_brands) ? $request->redirection_brands : null,
            'redirection_tags' => isset($request->redirection_tags) ? $request->redirection_tags : null,
        ]);
        
         if (isset($request->restrictions_id)) {
             foreach ($request->restrictions_id as $k => $value) {
                //   print_r($value['list']['value']);
                $data = [
                    'flash_id' => $falshsale->id,
                    'restriction_type' => isset($value['type']['value']) ? $value['type']['value'] : 0,
                    'select_include_exclude' => isset($value['list']['value']) ? $value['list']['value'] : 0,
                ];
        
                if($value['brand'] && $value['type']['value'] == 1){
                    $data['brand_id'] = implode(',', array_column($value['brand'], 'value'));
                }
                if($value['tag'] && $value['type']['value'] == 2){
                    $data['sub_tag_id'] = implode(',', array_column($value['tag'], 'value'));
                }
                if($value['product'] && $value['type']['value'] == 3){
                    $data['product_id'] = implode(',', array_column($value['product'], 'value'));
                }
                if($value['category'] && $value['type']['value'] == 4){
                    $data['category_id'] = implode(',', array_column($value['category'], 'value'));
                }
                FlashRestriction::create($data);
             }
         }
         return response()->json(['success' => true, 'message' => 'Flash Sale Has been created!']);
    }
    
    public function update(Request $request, $id) {
        if (isset($request->restrictions_id)) {
            $restriction_data = FlashRestriction::where('flash_id', '=',$id)->get();
            $restriction_data->each->delete();
            
            foreach ($request->restrictions_id as $k => $value) {
                //   print_r($value['list']['value']);
                $data = [
                    'flash_id' => $id,
                    'restriction_type' => isset($value['type']['value']) ? $value['type']['value'] : 0,
                    'select_include_exclude' => isset($value['list']['value']) ? $value['list']['value'] : 0,
                ];
        
                if($value['brand'] && $value['type']['value'] == 1){
                    $data['brand_id'] = implode(',', array_column($value['brand'], 'value'));
                }
                if($value['tag'] && $value['type']['value'] == 2){
                    $data['sub_tag_id'] = implode(',', array_column($value['tag'], 'value'));
                }
                if($value['product'] && $value['type']['value'] == 3){
                    $data['product_id'] = implode(',', array_column($value['product'], 'value'));
                }
                if($value['category'] && $value['type']['value'] == 4){
                    $data['category_id'] = implode(',', array_column($value['category'], 'value'));
                }
                FlashRestriction::create($data);
             }
        }
        
        $falshsale = FlashSale::whereId($id)->update([
            'name' => $request->get('name'),
            'name_arabic' => $request->get('name_arabic'),
            'notes' => isset($request->notes) ? $request->notes : null,
            'start_date' => isset($request->start_date) ? $request->start_date : null,
            'end_date' => isset($request->end_date) ? $request->end_date : null,
            'status' => isset($request->status) ? $request->status : 0,
            'quantity' => isset($request->quantity) ? $request->quantity : null,
            'left_quantity' => isset($request->quantity) ? $request->quantity : null,
            'discount_type' => isset($request->discount_type['value']) ? $request->discount_type['value'] : null,
            'discount_amount' => isset($request->discount_amount) ? $request->discount_amount : null,
            'image' => isset($request->image) ? $request->image : null,
            'image_app' => isset($request->image_app) ? $request->image_app : null,
            'redirection_type' => isset($request->redirection_type) ? $request->redirection_type : null,
            'redirection_products' => isset($request->redirection_products) ? $request->redirection_products : null,
            'redirection_categories' => isset($request->redirection_categories) ? $request->redirection_categories : null,
            'redirection_brands' => isset($request->redirection_brands) ? $request->redirection_brands : null,
            'redirection_tags' => isset($request->redirection_tags) ? $request->redirection_tags : null,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Flash Sale Has been updated!']);
    }
}
