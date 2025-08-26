<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TamkeenPremium;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\PremiumMembership;
use App\Traits\CrudTrait;

class TamkeenPremiumApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'tamkeen_premium';
    protected $relationKey = 'tamkeen_premium_id';


    public function model() {
        $data = ['limit' => -1, 'model' => TamkeenPremium::class, 'sort' => ['id','desc']];
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
        return ['membership_id' => 'memberships', 'featuredImage_id' => 'featuredImage:id,image'];
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
        $data = TamkeenPremium::select('id', 'name', 'name_arabic', 'status')->with('memberships')
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
        $tamkeenpremium = TamkeenPremium::create([
            'name' => $request->get('name'),
            'name_arabic' => $request->get('name_arabic'),
            'feature_image' => isset($request->feature_image) ? $request->feature_image : null,
            'status' => isset($request->status) ? $request->status : 0,
        ]);
        
         if (isset($request->membership) && count($request->membership) >= 1 && ($request->membership[0]['name'] != '' || $request->membership[0]['name_ar'] != '')) {
             foreach ($request->membership as $k => $value) {
                //   print_r($value['list']['value']);
                $data = [
                    'premium_id' => $tamkeenpremium->id,
                    'name' => isset($value['name']) ? $value['name'] : null,
                    'name_arabic' => isset($value['name_ar']) ? $value['name_ar'] : null,
                    'restriction_type' => isset($value['restriction_type']['value']) ? $value['restriction_type']['value'] : 0,
                ];
        
                if($value['brand_id'] && $value['restriction_type']['value'] == 1){
                    $data['brand_id'] = implode(',', array_column($value['brand_id'], 'value'));
                }
                if($value['product_id'] && $value['restriction_type']['value'] == 2){
                    $data['product_id'] = implode(',', array_column($value['product_id'], 'value'));
                }
                if($value['sub_tag_id'] && $value['restriction_type']['value'] == 3){
                    $data['sub_tag_id'] = implode(',', array_column($value['sub_tag_id'], 'value'));
                }
                if($value['category_id'] && $value['restriction_type']['value'] == 4){
                    $data['category_id'] = implode(',', array_column($value['category_id'], 'value'));
                }
                PremiumMembership::create($data);
             }
         }
         return response()->json(['success' => true, 'message' => 'Tamkeen Premium Has been created!']);
    }
    
    public function update(Request $request, $id) {
        if (isset($request->membership)) {
            $restriction_data = PremiumMembership::where('premium_id', '=',$id)->get();
            $restriction_data->each->delete();
            
            foreach ($request->membership as $k => $value) {
                $data = [
                    'premium_id' => $id,
                    'name' => isset($value['name']) ? $value['name'] : null,
                    'name_arabic' => isset($value['name_ar']) ? $value['name_ar'] : null,
                    'restriction_type' => isset($value['restriction_type']['value']) ? $value['restriction_type']['value'] : 0,
                ];
        
                if($value['brand_id'] && $value['restriction_type']['value'] == 1){
                    $data['brand_id'] = implode(',', array_column($value['brand_id'], 'value'));
                }
                if($value['product_id'] && $value['restriction_type']['value'] == 2){
                    $data['product_id'] = implode(',', array_column($value['product_id'], 'value'));
                }
                if($value['sub_tag_id'] && $value['restriction_type']['value'] == 3){
                    $data['sub_tag_id'] = implode(',', array_column($value['sub_tag_id'], 'value'));
                }
                if($value['category_id'] && $value['restriction_type']['value'] == 4){
                    $data['category_id'] = implode(',', array_column($value['category_id'], 'value'));
                }
                PremiumMembership::create($data);
             }
        }
        
        $tamkeenpremium = TamkeenPremium::whereId($id)->update([
            'name' => $request->get('name'),
            'name_arabic' => $request->get('name_arabic'),
            'feature_image' => isset($request->feature_image) ? $request->feature_image : null,
            'status' => isset($request->status) ? $request->status : 0,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Tamkeen Premium Has been updated!']);
    }
}
