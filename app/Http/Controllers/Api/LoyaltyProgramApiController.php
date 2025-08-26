<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoyaltyProgram;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\States;
use App\Models\RulesConditions;
use App\Models\LoyaltyRestrictions;
use App\Traits\CrudTrait;
use App\Helper\ConditionSetup_helper;

class LoyaltyProgramApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'loyalty_program';
    protected $relationKey = 'loyalty_program_id';


    public function model() {
        $data = ['limit' => -1, 'model' => LoyaltyProgram::class, 'sort' => ['id','desc']];
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
        return ['restrictions_id' => 'restrictions','conditions_id' => 'conditions'];
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
         'cities' => States::where('country_id','191')->get(['id as value', 'name as label']),
         ];
    }
    
    public function index(Request $request){
        $data = LoyaltyProgram::select('id', 'name', 'name_arabic', 'end_date', 'notes', 'status')->with('restrictions:id,loyalty_id,restriction_type')
        ->orderBy('id', 'desc')->get();
        
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
        $Loyalty = LoyaltyProgram::create([
            'name' => $request->get('name'),
            'name_arabic' => $request->get('name_arabic'),
            'notes' => isset($request->notes) ? $request->notes : null,
            'discount_devices' => isset($request->discount_devices) ? implode(',',$request->discount_devices) : null,
            'usage_limit' => isset($request->usage_limit) ? $request->usage_limit : null,
            'condition_match_status' => isset($request->conditiontype) ? $request->conditiontype : null,
            'start_date' => isset($request->start_date) ? $request->start_date : null,
            'end_date' => isset($request->end_date) ? $request->end_date : null,
            'status' => isset($request->status) ? $request->status : 0,
        ]);
        
        if (isset($request->restrictions)) {
             foreach ($request->restrictions as $k => $value) {
                //   print_r($value['list']['value']);
                $data = [
                    'loyalty_id' => $Loyalty->id,
                    'restriction_type' => isset($value['restriction_type']['value']) ? $value['restriction_type']['value'] : 0,
                    'extra_reward_points' => isset($value['points']) ? $value['points'] : null,
                ];
        
                if($value['brand_id'] && $value['restriction_type']['value'] == 1){
                    $data['brand_id'] = implode(',', array_column($value['brand_id'], 'value'));
                }
                if($value['sub_tag_id'] && $value['restriction_type']['value'] == 3){
                    $data['sub_tag_id'] = implode(',', array_column($value['sub_tag_id'], 'value'));
                }
                if($value['product_id'] && $value['restriction_type']['value'] == 2){
                    $data['product_id'] = implode(',', array_column($value['product_id'], 'value'));
                }
                if($value['category_id'] && $value['restriction_type']['value'] == 4){
                    $data['category_id'] = implode(',', array_column($value['category_id'], 'value'));
                }
                LoyaltyRestrictions::create($data);
             }
         }
         
         if (isset($request->conditiondata)) {
            $data = $request->all();
            $ruleid = $Loyalty->id;
            $moduletype = 3;
            ConditionSetup_helper::CreateConditionsetup($data,$ruleid, $moduletype);
        }
        return response()->json(['success' => true, 'message' => 'Loyality Program Has been created!']);
    }
    public function update(Request $request, $id) {
        
        if (isset($request->restrictions)) {
            $restriction_data = LoyaltyRestrictions::where('loyalty_id', '=',$id)->get();
            $restriction_data->each->delete();
            
            foreach ($request->restrictions as $k => $value) {
                //   print_r($value['list']['value']);
                $data = [
                    'loyalty_id' => $id,
                    'restriction_type' => isset($value['restriction_type']['value']) ? $value['restriction_type']['value'] : 0,
                    'extra_reward_points' => isset($value['points']) ? $value['points'] : null,
                ];
        
                if($value['brand_id'] && $value['restriction_type']['value'] == 1){
                    $data['brand_id'] = implode(',', array_column($value['brand_id'], 'value'));
                }
                if($value['sub_tag_id'] && $value['restriction_type']['value'] == 3){
                    $data['sub_tag_id'] = implode(',', array_column($value['sub_tag_id'], 'value'));
                }
                if($value['product_id'] && $value['restriction_type']['value'] == 2){
                    $data['product_id'] = implode(',', array_column($value['product_id'], 'value'));
                }
                if($value['category_id'] && $value['restriction_type']['value'] == 4){
                    $data['category_id'] = implode(',', array_column($value['category_id'], 'value'));
                }
                LoyaltyRestrictions::create($data);
             }
        }
        
        if (isset($request->conditiondata)) {
            $data = $request->all();
            $ruleid = $id;
            $moduletype = 3;
            ConditionSetup_helper::UpdateConditionsetup($data,$ruleid, $moduletype);
        }
        
         $Loyalty = LoyaltyProgram::whereId($id)->update([
            'name' => $request->get('name'),
            'name_arabic' => $request->get('name_arabic'),
            'notes' => isset($request->notes) ? $request->notes : null,
            'discount_devices' => isset($request->discount_devices) ? implode(',',$request->discount_devices) : null,
            'usage_limit' => isset($request->usage_limit) ? $request->usage_limit : null,
            'condition_match_status' => isset($request->conditiontype) ? $request->conditiontype : null,
            'start_date' => isset($request->start_date) ? $request->start_date : null,
            'end_date' => isset($request->end_date) ? $request->end_date : null,
            'status' => isset($request->status) ? $request->status : 0, 
        ]);
        
        return response()->json(['success' => true, 'message' => 'Loyalty Program Has been updated!']);
    }
}
